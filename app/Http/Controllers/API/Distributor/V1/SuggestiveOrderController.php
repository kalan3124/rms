<?php
namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use App\Models\SalesmanValidCustomer;
use App\Models\SfaSalesOrder;
use App\Models\SfaSalesOrderProduct;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuggestiveOrderController extends Controller{

     public function createSuggestOrder(){
          
          $begin = new \DateTime(date('Y-m-01',strtotime((new \Carbon\Carbon)->submonths(3))));
          $end = new \DateTime(date('Y-m-t').'-1 month');
          $interval = \DateInterval::createFromDateString('1 month');
          $period = new \DatePeriod($begin, $interval, $end);

          $user = Auth::user();

          $sale_chemist = SalesmanValidCustomer::where('u_id',$user->getKey())->get();

          $query = DB::table('sfa_sales_order as so');
          $query->select([
               'sop.product_id',
               'so.sales_order_amt',
               'so.order_date',
               'sop.sales_qty'
          ]);
          $query->join('sfa_sales_order_product as sop','sop.order_id','so.order_id');
          $query->whereIn('so.chemist_id',$sale_chemist->pluck('chemist_id')->all());
          $query->whereDate('so.order_date','>=',date('Y-m-01',strtotime((new \Carbon\Carbon)->submonths(3))));
          $query->whereDate('so.order_date','<=',date('Y-m-t',strtotime((new \Carbon\Carbon)->submonths(1))));
          $query->whereNotNull('sales_order_amt');
          // $query->groupBy('sop.product_id');
          // $query->groupBy(DB::raw('DATE(so.order_date)'));
          $results = $query->get();

          $sale_order = SfaSalesOrder::whereIn('chemist_id',$sale_chemist->pluck('chemist_id')->all())->get();
          $sale_pro = SfaSalesOrderProduct::whereIn('order_id',$sale_order->pluck('order_id')->all())->groupBy('product_id')->get();

          $sale_pro->transform(function($val) use($results){

               $amount =  $results->where('product_id',$val->product_id)->sum('sales_order_amt');
               $qty =  $results->where('product_id',$val->product_id)->sum('sales_qty');
               $count =  $results->where('product_id',$val->product_id)->count();

               return[
                    'product_id' => $val->product_id,
                    'amount' => $amount,
                    'qty' => $qty
               ];
          });

          return [
               'result' => true,
               'data' => $sale_pro
          ];
     }
}
?>