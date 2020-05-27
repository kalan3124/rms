<?php
namespace App\Http\Controllers\WebView\Sales;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Validator;
use App\Models\SalesmanValidCustomer;
use App\Models\Chemist;
use App\Models\Principal;
use App\Models\SalesmanValidPart;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;
use App\Traits\SalesTerritory;

class PrincipalWiseTargetVsAchiController extends Controller{
     use SalesTerritory;
     public function index(){
          return view('WebView/Sales.principal_wise_target');
     }

     public function getPrincipalWiseTarget(Request $request){

          $validation = Validator::make($request->all(),[
               'date_month'=>'required'
          ]);

          $time = time();

          if(!$validation->fails())
               $time = strtotime($request->input('date_month')."-01");

          $user = Auth::user();

        $products = SalesmanValidPart::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-01',$time))->whereDate('to_date','>=',date('Y-m-t',$time))->get();
        $chemists = SalesmanValidCustomer::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-01',$time))->whereDate('to_date','>=',date('Y-m-t',$time))->get();

        $invoice = DB::table('principal as pr')
                ->join('product as p','p.principal_id','pr.principal_id')
                ->join('invoice_line as il','il.product_id','p.product_id')
                ->leftJoin('latest_price_informations AS pi',function($query){
                    $query->on('pi.product_id','=','p.product_id');
                    $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                    })
                ->select([
                        'pr.principal_id',
                        DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                ])
                ->whereIn('il.product_id',$products->pluck('product_id')->all())
                ->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all())
                ->whereDate('il.invoice_date','>=',date('Y-m-01',$time))
                ->whereDate('il.invoice_date','<=',date('Y-m-t',$time))
                ->whereNull('il.deleted_at')
                ->whereNull('p.deleted_at')
                ->whereNull('pr.deleted_at')
                ->groupBy('pr.principal_id');

        $return = DB::table('principal as pr')
                ->join('product as p','p.principal_id','pr.principal_id')
                ->join('return_lines as il','il.product_id','p.product_id')
                ->leftJoin('latest_price_informations AS pi',function($query){
                    $query->on('pi.product_id','=','p.product_id');
                    $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                    })
                ->select([
                        'pr.principal_id',
                        DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                ])
                ->whereIn('il.product_id',$products->pluck('product_id')->all())
                ->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all())
                ->whereDate('il.invoice_date','>=',date('Y-m-01',$time))
                ->whereDate('il.invoice_date','<=',date('Y-m-t',$time))
                ->whereNull('il.deleted_at')
                ->whereNull('p.deleted_at')
                ->whereNull('pr.deleted_at')
                ->groupBy('pr.principal_id');


                $invoices = $invoice->get();
                $returns = $return->get();

                $allachivements = $invoices->concat($returns);

                $principal = Principal::whereIn('principal_id',$allachivements->pluck('principal_id')->all());

            $results = $principal->get();

            $target_product = SfaTargetProduct::with('product','product.principal')->whereDate('created_at','>=',date('Y-m-01',$time))->whereDate('created_at','<=',date('Y-m-t',$time))->get();

            $target_product->transform(function($val){
                return[
                        'principal_id' => $val->product->principal->principal_id,
                        'amount' => $val->stp_amount
                ];
            });

            $results->transform(function($row) use($invoices,$returns,$target_product){

                $target_pro = $target_product->where('principal_id',$row->principal_id)->sum('amount');

                $salesAchi = $invoices->where('principal_id',$row->principal_id)->sum('bdgt_value');
                $returnAchi = $returns->where('principal_id',$row->principal_id)->sum('bdgt_value');

                if(isset($salesAchi) && isset($returnAchi))
                    $prinAchi = $salesAchi - $returnAchi;

                if((isset($target_pro) && isset($prinAchi) && ($target_pro > 0 && $prinAchi > 0)))
                        $balance = $target_pro - $prinAchi;

                if((isset($target_pro) && isset($prinAchi) && ($target_pro > 0 && $prinAchi > 0)))
                        $achi_ = $prinAchi/$target_pro * 100;

                $return['principal'] = $row->principal_name;
                $return['target'] = isset($target_pro)?number_format($target_pro,2):0;
                $return['achi'] = isset($prinAchi)?number_format($prinAchi,2):0;
                $return['achi_%'] = isset($achi_)?number_format($achi_,2):0;
                $return['balance'] = isset($balance)?number_format($balance,2):0;

                return $return;
            });


          return view('WebView/Sales.principal_wise_target_search',['principalWiseTargets' => $results]);
     }
}

?>
