<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Area;
use App\Models\InvoiceLine;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\User;
use App\Models\SfaSrItineraryDetail;
use App\Models\SalesmanValidPart;
use App\Models\SalesmanValidCustomer;
use App\Models\SfaSalesOrder;
use App\Exceptions\WebAPIException;


class ProductivityReportController extends ReportController
{

    protected $title = "Productivity Report";

    protected $updateColumnsOnSearch = true;

    public function search(Request $request)
    {
        $values = $request->input('values');

        if (!isset($values['area'])) {
            throw new WebAPIException('Area field is required');
        }

        $query = Area::query();

        if (isset($values['area'])) {
            $query->where('ar_id', $values['area']);
        }
        $results = $query->get();

        $formattedResults = [];

        foreach ($results as $key => $row) {

            $users = User::where('u_tp_id', 10)->where('u_code', 'LIKE', '%' . $row->ar_code . '%')->get();


            foreach ($users as $key => $user) {

                $itinerary = SalesItinerary::where('u_id', $user->id)->where('s_i_year', date('Y'))->where('s_i_month', date('m'))->latest()->first();
                $itinerarydate = SalesItineraryDate::with('route')->where('s_i_id', $itinerary['s_i_id'])->where('s_id_date', date('d'))->first();
                $itinerarydateForMonth = SalesItineraryDate::with('route')->where('s_i_id', $itinerary['s_i_id'])->get();

                //get products section

                //by day
                $salesmanvalid = SalesmanValidPart::query();
                $salesmanvalid->select('product_id');
                $salesmanvalid->where('u_id', $user->id);
                $salesmanvalid->whereNull('deleted_at');
                $salesmanvalid->whereDate('from_date', '<=', date('Y-m-d'));
                $salesmanvalid->whereDate('to_date', '>=', date('Y-m-d'));


                $routeProducts = $salesmanvalid->get();

                $queryCus = SalesmanValidCustomer::query();
                $queryCus->select('chemist_id');
                $queryCus->where('u_id', $user->id);
                $queryCus->whereNull('deleted_at');
                $queryCus->whereDate('from_date', '<=', date('Y-m-d'));
                $queryCus->whereDate('to_date', '>=', date('Y-m-d'));

                $routeChemist = $queryCus->get();

                $invoicelinePro = DB::table('invoice_line as il')
                    ->whereIn('il.product_id', $routeProducts->pluck('product_id')->all())
                    ->whereIn('il.chemist_id', $queryCus->pluck('chemist_id')->all())
                    ->whereDate('il.invoice_date', '>=', date('Y-m-d'))
                    ->whereDate('il.invoice_date', '<=', date('Y-m-d'))
                    ->whereNull('il.deleted_at')
                    ->count(DB::raw('DISTINCT il.product_id'));

                //by month
                $salesmanvalidmonth = SalesmanValidPart::query();
                $salesmanvalidmonth->select('product_id');
                $salesmanvalidmonth->where('u_id', $user->id);
                $salesmanvalidmonth->whereNull('deleted_at');
                $salesmanvalidmonth->whereDate('from_date', '<=', date('Y-m-01'));
                $salesmanvalidmonth->whereDate('to_date', '>=', date('Y-m-t'));

                $routeProductsm = $salesmanvalid->get();

                $queryCusmonth = SalesmanValidCustomer::query();
                $queryCusmonth->select('chemist_id');
                $queryCusmonth->where('u_id', $user->id);
                $queryCusmonth->whereNull('deleted_at');
                $queryCusmonth->whereDate('from_date', '<=', date('Y-m-01'));
                $queryCusmonth->whereDate('to_date', '>=', date('Y-m-t'));

                $routeChemistm = $queryCusmonth->get();

                $invoicelineProm = DB::table('invoice_line as il')
                    ->whereIn('il.product_id', $routeProductsm->pluck('product_id')->all())
                    ->whereIn('il.chemist_id', $routeChemistm->pluck('chemist_id')->all())
                    ->whereDate('il.invoice_date', '>=', date('Y-m-01'))
                    ->whereDate('il.invoice_date', '<=', date('Y-m-t'))
                    ->whereNull('il.deleted_at')
                    ->count(DB::raw('DISTINCT il.product_id'));

                //get customers section

                //by day

                $invoicelineCus = DB::table('invoice_line as il')
                    // ->join('salesman_valid_customer as svc','svc.chemist_id','il.chemist_id')
                    ->whereIn('il.chemist_id', $routeChemist->pluck('chemist_id')->all())
                    ->whereIn('il.product_id', $routeProducts->pluck('product_id')->all())
                    ->whereDate('il.invoice_date', '>=', date('Y-m-d'))
                    ->whereDate('il.invoice_date', '<=', date('Y-m-d'))
                    ->whereNull('il.deleted_at')
                    ->count(DB::raw('DISTINCT il.chemist_id'));

                //by month
                $invoicelineCusm = DB::table('invoice_line as il')
                    // ->join('salesman_valid_customer as svc','svc.chemist_id','il.chemist_id')
                    ->whereIn('il.chemist_id', $routeChemistm->pluck('chemist_id')->all())
                    ->whereIn('il.product_id', $routeProductsm->pluck('product_id')->all())
                    ->whereDate('il.invoice_date', '>=', date('Y-m-01'))
                    ->whereDate('il.invoice_date', '<=', date('Y-m-t'))
                    ->whereNull('il.deleted_at')
                    ->count(DB::raw('DISTINCT il.chemist_id'));

                $itinerarydateForMonth->transform(function ($val) {
                    return [
                        'sum_of_shcl' => isset($val->route->route_schedule) ? $val->route->route_schedule : 0
                    ];
                });

                $c_sche_call = $itinerarydateForMonth->sum('sum_of_shcl');

                $ccall_rate = 0;
                $cc_call_rate = 0;
                $booking_rate = 0;
                $booking_ratem = 0;

                if ($invoicelinePro > 0 && $invoicelineCus > 0)
                    $booking_rate = $invoicelinePro / $invoicelineCus;

                if ($invoicelineProm > 0 && $invoicelineCusm > 0)
                    $booking_ratem = $invoicelineProm / $invoicelineCusm;

                if ($invoicelineCusm  && $c_sche_call > 0)
                    $cc_call_rate = $invoicelineCusm / $c_sche_call * 100;

                if (isset($itinerarydate->route->route_schedule) && $itinerarydate->route->route_schedule &&  $invoicelineCus > 0) {
                    $ccall_rate =  $invoicelineCus / $itinerarydate->route->route_schedule * 100;
                }

                /*Set Unic Id*/

                $count = $users->where('terr_code', $user->ar_id)->count();

                if ($key) {
                    $rd_code = $user->ar_id;
                    $prevRow = $users[$key - 1];
                    $prevTm_id = $prevRow->rd_code;

                    if ($rd_code != $prevTm_id) {
                        $rowNew['terr_code'] = $user->rd_code;
                        $rowNew['terr_code_rowspan'] = $count;
                        $rowNew['terr_name'] = $user->rd_code;
                        $rowNew['terr_name_rowspan'] = $user->rd_code;
                    } else {
                        $rowNew['terr_code'] = null;
                        $rowNew['terr_code_rowspan'] = 0;
                        $rowNew['terr_name'] = null;
                        $rowNew['terr_name_rowspan'] = 0;
                    }
                } else {
                    $rowNew['terr_code'] = $user->rd_code;
                    $rowNew['terr_code_rowspan'] = $count;
                    $rowNew['terr_name'] = $user->rd_code;
                    $rowNew['terr_name_rowspan'] = $count;
                }

                $rowNew['terr_code'] = isset($row->ar_code) ? $row->ar_code : '-';
                $rowNew['terr_name'] = isset($row->ar_name) ? $row->ar_name : '-';
                $rowNew['exe_code'] = $user->u_code;
                $rowNew['exe_name'] = $user->name;

                $rowNew['sche_call'] = isset($itinerarydate->route->route_schedule) ? $itinerarydate->route->route_schedule : '0';
                $rowNew['no_of_pro'] = isset($invoicelinePro) ? $invoicelinePro : '0';
                $rowNew['no_of_cus'] = isset($invoicelineCus) ? $invoicelineCus : '0';
                //  'booking_rate' => isset($invoicelinePro) / isset($invoicelineCus),
                $rowNew['booking_rate'] = number_format($booking_rate, 2);
                $rowNew['booking_rate_new'] = $booking_rate;
                $rowNew['call_rate'] = number_format($ccall_rate, 2);

                $rowNew['c_sche_call'] = $c_sche_call ? $c_sche_call : '0';
                $rowNew['c_no_of_pro'] = isset($invoicelineProm) ? $invoicelineProm : '0';
                $rowNew['c_no_of_cus'] = isset($invoicelineCusm) ? $invoicelineCusm : '0';
                //  'c_booking_rate' =>isset($invoicelineProm) / isset($invoicelineCusm),
                $rowNew['c_booking_rate'] = number_format($booking_ratem, 2);
                $rowNew['c_booking_rate_new'] = $booking_ratem;
                $rowNew['c_call_rate'] = number_format($cc_call_rate, 2);
                $rowNew['c_call_rate_new'] = $cc_call_rate;

                $new[] = $rowNew;
            }
        }

        $results = collect($new);

        $booking = 0;
        $cbooking = 0;
        $cc = 0;
        $ccc = 0;

        if ($results->sum('no_of_pro') && $results->sum('no_of_cus') > 0)
            $booking = $results->sum('no_of_pro') / $results->sum('no_of_cus');

        if ($results->sum('c_no_of_pro') && $results->sum('c_no_of_cus') > 0)
            $cbooking = $results->sum('c_no_of_pro') / $results->sum('c_no_of_cus');

        if ($results->sum('sche_call') && $results->sum('no_of_cus') > 0)
            $cc = $results->sum('no_of_cus') / $results->sum('sche_call') * 100;

        if ($results->sum('c_sche_call') && $results->sum('c_no_of_cus') > 0)
            $ccc = $results->sum('c_no_of_cus') / $results->sum('c_sche_call') * 100;

        $new[]  = [
            'special' => true,
            'terr_code' => NULL,
            'terr_name' => NULL,
            'exe_code' => 'Sub Total',
            'exe_name' => NULL,
            'sche_call' => $results->sum('sche_call'),
            'no_of_pro' => $results->sum('no_of_pro'),
            'no_of_cus' => $results->sum('no_of_cus'),
            'booking_rate' => number_format($booking, 2),
            'call_rate' => number_format($cc, 2),
            'c_sche_call' => $results->sum('c_sche_call'),
            'c_no_of_pro' => $results->sum('c_no_of_pro'),
            'c_no_of_cus' => $results->sum('c_no_of_cus'),
            'c_booking_rate' => number_format($cbooking, 2),
            'c_call_rate' => number_format($ccc, 2),

        ];

        return [
            'results' => $new,
            'count' => 0
        ];
    }

    protected function getAdditionalHeaders($request)
    {

        $first_row = [
            "title" => "",
            "colSpan" => 4
        ];

        $second_row = [
            "title" => "Current Date",
            "colSpan" => 5
        ];

        $third_row = [
            "title" => "Cummulative for the MONTH",
            "colSpan" => 5
        ];

        $columns = [[
            $first_row,
            $second_row,
            $third_row
        ]];
        return $columns;
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {

        $columnController->text('terr_code')->setLabel('Territory Code');
        $columnController->text('terr_name')->setLabel('Territory Name');
        $columnController->text('exe_code')->setLabel('Executive Code');
        $columnController->text('exe_name')->setLabel('Executive Name');

        $columnController->number('sche_call')->setLabel('Schedule calls');
        $columnController->number('no_of_pro')->setLabel('No of products');
        $columnController->number('no_of_cus')->setLabel('No of customer');
        $columnController->number('booking_rate')->setLabel('Booking Rate');
        $columnController->number('call_rate')->setLabel('Call Rate %');

        $columnController->number('c_sche_call')->setLabel('Schedule calls');
        $columnController->number('c_no_of_pro')->setLabel('No of products');
        $columnController->number('c_no_of_cus')->setLabel('No of customer');
        $columnController->number('c_booking_rate')->setLabel('Booking Rate');
        $columnController->number('c_call_rate')->setLabel('Call Rate %');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('area')->setLabel('Area')->setLink('area')->setValidations('');
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type')])->setValidations('');
        $inputController->date('s_date')->setLabel('From')->setLink('s_date');
        $inputController->date('e_date')->setLabel('To')->setLink('e_date');

        $inputController->setStructure([
            ['area']
        ]);
    }
}
