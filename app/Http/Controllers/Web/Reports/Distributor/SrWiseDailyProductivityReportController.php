<?php
namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Area;
use App\Models\DistributorSalesRep;
use App\Models\DistributorSrCustomer;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SrWiseDailyProductivityReportController extends ReportController
{
    protected $title = "DSR Productivity Report";

    protected $updateColumnsOnSearch = true;

    public function search(Request $request)
    {
        $values = $request->input('values');
        // if (!isset($values['user'])) {
        //     throw new WebAPIException('Distributor field is required');
        // }

        $query = User::where('u_tp_id',14);
        if (isset($values['user'])) {
            $query->where('id', $values['user']);
        }
        $results = $query->get();
        foreach ($results as $key => $row) {
            // $dsr = DistributorSalesRep::where('dis_id',$row->id)->groupBy('sr_id')->get();
            $dsr = DB::table('distributor_sales_rep AS dsr')
            ->join('users AS u','u.id','dsr.sr_id')
            ->whereNull('dsr.deleted_at')
            ->whereNull('u.deleted_at')
            ->where('dsr.dis_id',$row->id)
            ->groupBy('dsr.sr_id')
            ->get();

            $users = User::where('u_tp_id',15)->whereIn('id',$dsr->pluck('sr_id')->all())->get();

            foreach ($users as $key => $user) {

                $count = $dsr->unique()->count();

                if($key){
                   $rd_code = $user->dis_id;
                   $prevRow = $users[$key-1];
                   $prevTm_id = $prevRow->rd_code;

                   if($rd_code != $prevTm_id){
                        $rowNew['dis_code'] = $user->rd_code;
                        $rowNew['dis_code_rowspan'] = $count;
                        $rowNew['dis_name'] = $user->rd_code;
                        $rowNew['dis_name_rowspan'] = $user->rd_code;
                   } else {
                        $rowNew['dis_code'] = null;
                        $rowNew['dis_code_rowspan'] = 0;
                        $rowNew['dis_name'] = null;
                        $rowNew['dis_name_rowspan'] = 0;
                   }
                } else {
                   $rowNew['dis_code'] = $user->rd_code;
                   $rowNew['dis_code_rowspan'] = $count;
                   $rowNew['dis_name'] = $user->rd_code;
                   $rowNew['dis_name_rowspan'] = $count;
                }

                $rowNew['dis_code'] = isset($row->u_code) ? $row->u_code : '-';
                $rowNew['dis_name'] = isset($row->name) ? $row->name : '-';
                $rowNew['sr_code'] = $user->u_code;
                $rowNew['sr_name'] = $user->name;

                if($request->has('values.s_date') && $request->has('values.e_date')){
                    $period = CarbonPeriod::create(date('Y-m-d',strtotime($request->input('values.s_date'))), date('Y-m-d',strtotime($request->input('values.e_date'))));
                } else {
                    $period = CarbonPeriod::create(date('Y-m-d'), date('Y-m-d'));
                }
                $totalProductCount = 0;
                $totalCustomerCount = 0;
                $totalOrderValue = 0;
                foreach ($period as $date) {
                    $itinerary = SalesItinerary::where('u_id', $user->id)->where('s_i_year', $date->format('Y'))->where('s_i_month', $date->format('m'))->whereNotNull('s_i_aprvd_at')->latest()->first();
                    $itinerarydate = SalesItineraryDate::with('route')->where('s_i_id', $itinerary['s_i_id'])->where('s_id_date', $date->format('d'))->first();

                    $rowNew[$date->format('Y-m-d').'_route_name'] = $itinerarydate['route']?$itinerarydate['route']['route_name']:"-";

                    $sfaOrders = DB::table('distributor_sales_order AS so')
                    ->join('distributor_sales_order_products AS sp','sp.dist_order_id', 'so.dist_order_id')
                    ->whereDate('so.order_date', '=', $date->format('Y-m-d'))
                    ->where('so.u_id',$user->id)
                    ->whereNull('so.deleted_at')
                    ->whereNull('sp.deleted_at')
                    ->select([
                        'so.dc_id AS chemist_id',
                        'sp.product_id',
                        DB::raw('(sp.sales_qty * sp.price) as OrderValue')
                    ])
                    ->get();


                    $sfaUniqueCustomers = $sfaOrders->unique('chemist_id');
                    $sfaUniqueCustomers->values()->all();
                    $sfaUniqueCustomers = $sfaUniqueCustomers->count();

                    $sfaUniqueProducts = $sfaOrders->unique(function ($item) {
                        return $item->chemist_id.$item->product_id;
                    });
                    $sfaUniqueProducts->values()->all();
                    $sfaUniqueProducts = $sfaUniqueProducts->count();

                    $sfaOrderValue = $sfaOrders->sum('OrderValue');

                    $rowNew[$date->format('Y-m-d').'_no_of_cus'] = isset($sfaUniqueCustomers) ? $sfaUniqueCustomers : '0';
                    $rowNew[$date->format('Y-m-d').'_no_of_pro'] = isset($sfaUniqueProducts) ? $sfaUniqueProducts : '0';
                    $rowNew[$date->format('Y-m-d').'_so_amt'] = isset($sfaOrderValue) ? number_format($sfaOrderValue,2) : '0';
                    $rowNew[$date->format('Y-m-d').'_so_amt_hdn'] = isset($sfaOrderValue) ? round($sfaOrderValue,2) : '0';

                    $totalCustomerCount += $sfaUniqueCustomers;
                    $totalProductCount += $sfaUniqueProducts;
                    $totalOrderValue += $sfaOrderValue;
                }
                $rowNew['total_no_of_cus'] = $totalCustomerCount;
                $rowNew['total_no_of_pro'] = $totalProductCount;
                $rowNew['total_so_amt'] = number_format($totalOrderValue, 2);
                $rowNew['total_so_amt_hdn'] = round($totalOrderValue, 2);

                $newResult[]=$rowNew;
            }

        }

        $results = collect($newResult);

        $newGrand = [];
        $newGrand['special'] = true;
        $newGrand['dis_code'] = 'Grand Total';
        $newGrand['dis_name'] = NULL;
        $newGrand['sr_code'] = NULL;
        $newGrand['sr_name'] = NULL;
        foreach ($period as $date) {
            $newGrand[$date->format('Y-m-d').'_no_of_cus'] = $results->sum($date->format('Y-m-d').'_no_of_cus');
            $newGrand[$date->format('Y-m-d').'_no_of_pro'] = $results->sum($date->format('Y-m-d').'_no_of_pro');
            $newGrand[$date->format('Y-m-d').'_so_amt'] = number_format($results->sum($date->format('Y-m-d').'_so_amt_hdn'),2);
        }
        $newGrand['total_no_of_cus'] = $results->sum('total_no_of_cus');
        $newGrand['total_no_of_pro'] = $results->sum('total_no_of_pro');
        $newGrand['total_so_amt'] = number_format($results->sum('total_so_amt_hdn'),2);

        $results->push($newGrand);

        return [
            'results' => $results,
            'count' => 0
        ];

    }

    protected function getAdditionalHeaders($request)
    {
        $columns = array();
        $column_array[] = array(
            'title' => "",
            'colSpan' => 4
        );
        if($request->has('values.s_date') && $request->has('values.e_date')){
            $period = CarbonPeriod::create(date('Y-m-d',strtotime($request->input('values.s_date'))), date('Y-m-d',strtotime($request->input('values.e_date'))));
        } else {
            $period = CarbonPeriod::create(date('Y-m-d'), date('Y-m-d'));
        }
        foreach ($period as $date) {
            $column_array[] = array(
                'title' => $date->format('Y-m-d'),
                'colSpan' => 4
            );
        }
        $column_array[] = array(
            'title' => "Executive wise Cumulative Sales Report",
            'colSpan' => 3
        );
        array_push($columns, $column_array);
        return $columns;
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('dis_code')->setLabel('Distributor Code');
        $columnController->text('dis_name')->setLabel('Distributor Name');
        $columnController->text('sr_code')->setLabel('Executive Code');
        $columnController->text('sr_name')->setLabel('Executive Name');

        if($request->has('values.s_date') && $request->has('values.e_date')){
            $period = CarbonPeriod::create(date('Y-m-d',strtotime($request->input('values.s_date'))), date('Y-m-d',strtotime($request->input('values.e_date'))));
        } else {
            $period = CarbonPeriod::create(date('Y-m-d'), date('Y-m-d'));
        }
        foreach ($period as $date) {
            $columnController->text($date->format('Y-m-d').'_route_name')->setLabel('Route Name');
            $columnController->number($date->format('Y-m-d').'_no_of_cus')->setLabel('No Of Customers');
            $columnController->number($date->format('Y-m-d').'_no_of_pro')->setLabel('No Of Products');
            $columnController->number($date->format('Y-m-d').'_so_amt')->setLabel('SFA Order Value');
        }

            $columnController->number('total_no_of_cus')->setLabel('Total No Of Customers');
            $columnController->number('total_no_of_pro')->setLabel('Total No Of Products');
            $columnController->number('total_so_amt')->setLabel('Total SFA Order Value');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('user')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')]);
        $inputController->date('s_date')->setLabel('From')->setLink('s_date');
        $inputController->date('e_date')->setLabel('To')->setLink('e_date');

        $inputController->setStructure([
            ['user'],
            ['s_date','e_date']
        ]);
    }
}
