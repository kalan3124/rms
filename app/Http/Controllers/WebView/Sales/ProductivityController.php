<?php
namespace App\Http\Controllers\WebView\Sales;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use App\Models\Area;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SfaSrItineraryDetail;
use App\Models\SalesmanValidPart;
use App\Models\SalesmanValidCustomer;
use App\Models\SfaSalesOrder;

class ProductivityController extends Controller{
    
     public function getProductivity(Request $request){
          $user = Auth::user();

          $arr2 = str_split($user->u_code, 4);

          $query = Area::query();

          $query->where('ar_code',$arr2[0]);
          $results = $query->get();
          
          $formattedResults = [];

          foreach ($results as $key => $row) {

                $itinerary = SalesItinerary::where('u_id', $user->id)->where('s_i_year', date('Y'))->where('s_i_month', date('m'))->latest()->first();
                $itinerarydate = SalesItineraryDate::with('route')->where('s_i_id', $itinerary['s_i_id'])->where('s_id_date', date('d'))->first();
                $itinerarydateForMonth = SalesItineraryDate::with('route')->where('s_i_id', $itinerary['s_i_id'])->get();

                //get products section
                //by day
                $salesmanvalid = SalesmanValidPart::query();
                $salesmanvalid->select('product_id');
                $salesmanvalid->where('u_id', $user->id);
                $salesmanvalid->whereDate('from_date', '<=', date('Y-m-d'));
                $salesmanvalid->whereDate('to_date', '>=', date('Y-m-d'));

                $routeProducts = $salesmanvalid->get();

                $invoicelinePro = DB::table('sfa_sales_order as s')
                ->join('sfa_sales_order_product as sp','s.order_id','sp.order_id')
                ->whereIn('sp.product_id', $routeProducts->pluck('product_id')->all())
                    ->whereDate('s.order_date', '>=', date('Y-m-d'))
                    ->whereDate('s.order_date', '<=', date('Y-m-d'))
                    ->whereNull('s.deleted_at')
                    ->whereNull('sp.deleted_at')
                    ->count(DB::raw('DISTINCT sp.product_id'));



                //by month
                $salesmanvalidmonth = SalesmanValidPart::query();
                $salesmanvalidmonth->select('product_id');
                $salesmanvalidmonth->where('u_id', $user->id);
                $salesmanvalidmonth->whereDate('from_date', '<=', date('Y-m-01'));
                $salesmanvalidmonth->whereDate('to_date', '>=', date('Y-m-d'));

                $routeProducts = $salesmanvalid->get();

                $invoicelineProm = DB::table('sfa_sales_order as s')
                    ->join('sfa_sales_order_product as sp','s.order_id','sp.order_id')
                    ->whereIn('sp.product_id', $routeProducts->pluck('product_id')->all())
                    ->whereDate('s.order_date', '>=', date('Y-m-01'))
                    ->whereDate('s.order_date', '<=', date('Y-m-t'))
                    ->whereNull('s.deleted_at')
                    ->whereNull('sp.deleted_at')
                    ->count(DB::raw('DISTINCT sp.product_id'));

                //get customers section
                //by day
                $queryCus = SalesmanValidCustomer::query();
                $queryCus->select('chemist_id');
                $queryCus->where('u_id', $user->id);
                $queryCus->whereDate('from_date', '<=', date('Y-m-d'));
                $queryCus->whereDate('to_date', '>=', date('Y-m-d'));

                $routeChemist = $queryCus->get();

                $invoicelineCus = SfaSalesOrder::whereIn('chemist_id', $routeChemist->pluck('chemist_id')->all())
                    ->whereDate('order_date', '>=', date('Y-m-d'))
                    ->whereDate('order_date', '<=', date('Y-m-d'))
                    ->count(DB::raw('DISTINCT chemist_id'));


                //by month
                $queryCusmonth = SalesmanValidCustomer::query();
                $queryCusmonth->select('chemist_id');
                $queryCusmonth->where('u_id', $user->id);
                $queryCusmonth->whereDate('from_date', '<=', date('Y-m-01'));
                $queryCusmonth->whereDate('to_date', '>=', date('Y-m-d'));

                $routeChemistm = $queryCus->get();

                $invoicelineCusm = SfaSalesOrder::whereIn('chemist_id', $routeChemistm->pluck('chemist_id')->all())
                    ->whereDate('order_date', '>=', date('Y-m-01'))
                    ->whereDate('order_date', '<=', date('Y-m-t'))
                    ->count(DB::raw('DISTINCT chemist_id'));



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

                    $formattedResults [] = [
                         'terr_code' => isset($row->ar_code)?$row->ar_code:'-',
                         'terr_name' => isset($row->ar_name)?$row->ar_name:'-',
                         'exe_code' => $user->u_code,
                         'exe_name' => $user->name,

                         'sche_call' => isset($itinerarydate->route->route_schedule) ? $itinerarydate->route->route_schedule : 0,
                         'no_of_pro' => isset($invoicelinePro) ? $invoicelinePro :0,
                         'no_of_cus' => isset($invoicelineCus) ? $invoicelineCus:0,
                         'booking_rate' => number_format($booking_rate, 2),
                         'call_rate' => $ccall_rate,

                         'c_sche_call' => $c_sche_call ? $c_sche_call:0,
                         'c_no_of_pro' => isset($invoicelineProm) ? $invoicelineProm:0,
                         'c_no_of_cus' => isset($invoicelineCusm) ? $invoicelineCusm:0,
                         'c_booking_rate' => number_format($booking_ratem, 2),
                         'c_call_rate' => number_format($cc_call_rate, 2),
                         'diff' => 0
                    ];
          }
          // return $formattedResults;

          return view('WebView/Sales.productivity_report',['productivities' => $formattedResults]);
     }
}

?>