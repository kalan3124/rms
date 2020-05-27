<?php
namespace App\Http\Controllers\API\Sales\V1;

use App\Http\Controllers\Controller;
use App\Models\Chemist;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\SfaSalesOrder;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaDailyTarget;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;
use Illuminate\Support\Facades\DB;
use App\Traits\SalesTerritory;

class TargetController extends Controller{

    use SalesTerritory;

    public function getSrTarget(Request $request){

        $user= Auth::user();

        $sales = SfaSalesOrder::where('u_id',$user->getKey())->whereDate('order_date',date('Y-m-d'))->count('order_no');
        $invoice = InvoiceLine::where('salesman_code',$user->u_code)->whereDate('invoice_date',date('Y-m-d'))->count('invoice_no');

        $day_target = SfaDailyTarget::where('sr_code',$user->getkey())->whereDate('target_day',date('Y-m-d'))->first();
        $target = SfaTarget::where('u_id',$user->getKey())->where('trg_year',date('Y'))->where('trg_month',date('m'))->latest()->first();
        $target_pro = SfaTargetProduct::where('sfa_trg_id',$target['sfa_trg_id'])->sum('stp_amount');

        $productsMonth = SalesmanValidPart::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-01'))->whereDate('to_date','>=',date('Y-m-t'))->get();
        $chemistsMonth = SalesmanValidCustomer::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-01'))->whereDate('to_date','>=',date('Y-m-t'))->get();

        $productsDay = SalesmanValidPart::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-d'))->whereDate('to_date','>=',date('Y-m-d'))->get();
        $chemistsDay = SalesmanValidCustomer::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-d'))->whereDate('to_date','>=',date('Y-m-d'))->get();

        $dayAchievement = $this->makeQuery($productsDay,$chemistsDay,date('Y-m-d'),date('Y-m-d'));
        $monthAchievement = $this->makeQuery($productsMonth,$chemistsMonth,date('Y-m-01'),date('Y-m-t'));

        if(isset($target_pro)){
            $month_target = $target_pro;
        }

        $targetInfo = [
            'dayAchievement'=>isset($dayAchievement)?$dayAchievement:0,
            'dayTarget'=>isset($day_target->day_target)?$day_target->day_target:0,
            'monthAchievement'=>isset($monthAchievement)?$monthAchievement:0,
            'monthTarget'=>isset($month_target)?$month_target:0,
            'totalInvoices'=>$invoice,
            'totalSalesOrders'=>$sales
        ];

        return response()->json([
            "result" => true,
            "data" => $targetInfo
        ]);

    }

    public function getMonthRoutesForUser($user){
        $sfa_itinerary = SalesItinerary::where('u_id',$user->getKey())->where('s_i_year',date('Y'))->where('s_i_month',date('m'))->latest()->first();
        $sfa_itinerary_date = SalesItineraryDate::with('route')->where('s_i_id',$sfa_itinerary['s_i_id'])->get();

        return $sfa_itinerary_date;
    }

    protected function makeQuery($products,$chemists,$fromDate,$toDate){

        $invoice = DB::table('product as p')
                    ->join('invoice_line as il','il.product_id','p.product_id')
                    ->leftJoin('latest_price_informations AS pi',function($query){
                        $query->on('pi.product_id','=','p.product_id');
                        $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                            })
                    ->select([
                         'il.product_id',
                         DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                         DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                    ])
                    ->whereIn('il.product_id',$products->pluck('product_id')->all())
                    ->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all())
                    ->whereDate('il.invoice_date','>=',$fromDate)
                    ->whereDate('il.invoice_date','<=',$toDate)
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pi.deleted_at')
                    ->groupBy('il.product_id');

          $return = DB::table('product as p')
                    ->join('return_lines as il','il.product_id','p.product_id')
                    ->leftJoin('latest_price_informations AS pi',function($query){
                        $query->on('pi.product_id','=','p.product_id');
                        $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                            })
                    ->select([
                         'il.product_id',
                         DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS return_qty'),
                         DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                    ])
                    ->whereIn('il.product_id',$products->pluck('product_id')->all())
                    ->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all())
                    ->whereDate('il.invoice_date','>=',$fromDate)
                    ->whereDate('il.invoice_date','<=',$toDate)
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pi.deleted_at')
                    ->groupBy('il.product_id');

                    $invoices = $invoice->get();
                    $returns = $return->get();

        $inv = 0;
        $ret = 0;
        foreach ($invoices as $key => $value) {
            $inv += $value->bdgt_value;
        }

        foreach ($returns as $key => $value) {
            $ret += $value->bdgt_value;
        }
        $total = $inv - $ret;
        return $total;

    }
}
