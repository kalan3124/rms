<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Area;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaSalesOrder;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SrWiseDailyProductivityReportController extends ReportController
{

    protected $title = "SR Productivity Report";

     protected $updateColumnsOnSearch = true;

    public function search(Request $request)
    {
        $values = $request->input('values');
        if (!isset($values['area'])) {
            throw new WebAPIException('Area field is required');
        }
        // if(!isset($values['s_date']) && isset($values['e_date'])){
        //     throw new WebAPIException('Date Range fields are required');
        // }

        $query = Area::query();
        if (isset($values['area'])) {
            $query->where('ar_id', $values['area']);
        }
        $results = $query->get();
        foreach ($results as $key => $row) {
            $users = User::where('u_tp_id', 10)->where('u_code', 'LIKE', '%' . $row->ar_code . '%')->get();

            foreach ($users as $key => $user) {

                $count = $users->where('territory_code',$user->ar_id)->count();

                if($key){
                   $rd_code = $user->ar_id;
                   $prevRow = $users[$key-1];
                   $prevTm_id = $prevRow->rd_code;

                   if($rd_code != $prevTm_id){
                        $rowNew['territory_code'] = $user->rd_code;
                        $rowNew['territory_code_rowspan'] = $count;
                        $rowNew['territory_name'] = $user->rd_code;
                        $rowNew['territory_name_rowspan'] = $user->rd_code;
                   } else {
                        $rowNew['territory_code'] = null;
                        $rowNew['territory_code_rowspan'] = 0;
                        $rowNew['territory_name'] = null;
                        $rowNew['territory_name_rowspan'] = 0;
                   }
                } else {
                   $rowNew['territory_code'] = $user->rd_code;
                   $rowNew['territory_code_rowspan'] = $count;
                   $rowNew['territory_name'] = $user->rd_code;
                   $rowNew['territory_name_rowspan'] = $count;
                }

                $rowNew['territory_code'] = isset($row->ar_code) ? $row->ar_code : '-';
                $rowNew['territory_name'] = isset($row->ar_name) ? $row->ar_name : '-';
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
                    /**
                     * GET TODAY ROUTE FOR SR
                     */
                    $itinerary = SalesItinerary::where('u_id', $user->id)->where('s_i_year', $date->format('Y'))->where('s_i_month', $date->format('m'))->whereNotNull('s_i_aprvd_at')->latest()->first();
                    $itinerarydate = SalesItineraryDate::with('route')->where('s_i_id', $itinerary['s_i_id'])->where('s_id_date', $date->format('d'))->first();

                    $rowNew[$date->format('Y-m-d').'_route_name'] = $itinerarydate['route']?$itinerarydate['route']['route_name']:"-";
                    /**
                     * END ROUTE
                     */

                    /**
                     * GET CUSTOMERS
                     */
                    $assignedProducts = SalesmanValidPart::whereDate('from_date','<=',$date->format('Y-m-d'))
                    ->whereDate('to_date','>=',$date->format('Y-m-d'))
                    ->where('u_id', $user->id)
                    ->get();

                    $assignedCustomers = SalesmanValidCustomer::whereDate('from_date','<=',$date->format('Y-m-d'))
                    ->whereDate('to_date','>=',$date->format('Y-m-d'))
                    ->where('u_id', $user->id)
                    ->get();

                    $sfaOrders = DB::table('sfa_sales_order AS so')
                    ->join('sfa_sales_order_product AS sp','sp.order_id', 'so.order_id')
                    ->whereIn('so.chemist_id', $assignedCustomers->pluck('chemist_id')->all())
                    ->whereIn('sp.product_id', $assignedProducts->pluck('product_id')->all())
                    ->whereDate('so.order_date', '=', $date->format('Y-m-d'))
                    ->where('so.u_id',$user->id)
                    ->whereNull('so.deleted_at')
                    ->whereNull('sp.deleted_at')
                    ->select([
                        'so.chemist_id',
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
        $newGrand['territory_code'] = 'Grand Total';
        $newGrand['territory_name'] = NULL;
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

        $columnController->text('territory_code')->setLabel('Territory Code');
        $columnController->text('territory_name')->setLabel('Territory Name');
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
        $inputController->ajax_dropdown('area')->setLabel('Area')->setLink('area')->setValidations('');
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type')])->setValidations('');
        $inputController->date('s_date')->setLabel('From')->setLink('s_date');
        $inputController->date('e_date')->setLabel('To')->setLink('e_date');

        $inputController->setStructure([
            ['area'],
            ['s_date','e_date']
        ]);
    }
}

