<?php
namespace App\Http\Controllers\WebView\Sales;

use App\Http\Controllers\Controller;
use App\Models\Product;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Validator;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;
use App\Traits\SalesTerritory;

class UnitWiseTargetVsAchiController extends Controller{
    use SalesTerritory;
     public function index(){
          return view('WebView/Sales.unit_wise_target');
     }

     public function getUnitWiseTarget(Request $request){

          $validation = Validator::make($request->all(),[
               'date_month'=>'required'
          ]);

          $time = time();

          if(!$validation->fails())
               $time = strtotime($request->input('date_month')."-01");

          $user = Auth::user();

          $products = SalesmanValidPart::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-01',$time))->whereDate('to_date','>=',date('Y-m-t',$time))->get();
          $chemists = SalesmanValidCustomer::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-01',$time))->whereDate('to_date','>=',date('Y-m-t',$time))->get();

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
                    ->whereDate('il.invoice_date','>=',date('Y-m-01',$time))
                    ->whereDate('il.invoice_date','<=',date('Y-m-t',$time))
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
                    ->whereDate('il.invoice_date','>=',date('Y-m-01',$time))
                    ->whereDate('il.invoice_date','<=',date('Y-m-t',$time))
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pi.deleted_at')
                    ->groupBy('il.product_id');

                    $invoices = $invoice->get();
                    $returns = $return->get();

                    $allachivements = $invoices->concat($returns);

                    $product = Product::whereIn('product_id',$allachivements->pluck('product_id')->all());
                    $results = $product->get();


                    $query = SfaTarget::query();
                    $query->where('u_id',$user->getKey());
                    $query->where('trg_year',date('Y',$time));
                    $query->where('trg_month',date('m',$time));
                    $query->latest();
                    $target_user = $query->first();

                    $target_user = SfaTargetProduct::where('sfa_trg_id',$target_user['sfa_trg_id'])->get();

                    $target_user->transform(function($val){
                         return[
                              'product_id' => $val->product_id,
                              'stp_qty' => $val->stp_qty
                         ];
                    });

                    $productAchi = 0;
                    $balance = 0;
                    $achi_ = 0;
                    $results->transform(function($val) use($invoices,$returns,$target_user,$productAchi,$balance,$achi_){

                         if($target_user)
                              $target_pro = $target_user->where('product_id',$val->product_id)->sum('stp_qty');

                         $salesAchi = $invoices->where('product_id',$val->product_id)->sum('net_qty');
                         $returnAchi = $returns->where('product_id',$val->product_id)->sum('return_qty');

                         if(isset($salesAchi) && isset($returnAchi))
                              $productAchi = $salesAchi - $returnAchi;

                         if((isset($target_pro) && isset($productAchi) && ($target_pro > 0 && $productAchi > 0)))
                              $balance = $target_pro - $productAchi;

                         if((isset($target_pro) && isset($productAchi) && ($target_pro > 0 && $productAchi > 0)))
                              $achi_ = $productAchi/$target_pro * 100;

                         return [
                              'pro_code' => $val->product_code,
                              'pro_name' => $val->product_name,
                              'target' => isset($target_pro)?$target_pro:0,
                              'achi' => $productAchi?round($productAchi,2):0,
                              'ach_%' => $achi_?number_format($achi_,2):'0.00',
                              'balance' =>  $balance?number_format($balance,2):'0.00',
                         ];
                    });

          return view('WebView/Sales.unit_wise_target_search',['townWiseTargets' => $results]);
     }
}

?>
