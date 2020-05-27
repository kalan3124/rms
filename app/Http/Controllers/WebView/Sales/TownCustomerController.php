<?php
namespace App\Http\Controllers\WebView\Sales;

use App\Http\Controllers\Controller;
use App\Models\Chemist;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaCustomerTarget;
use App\Models\SubTown;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Validator;
use App\Traits\SalesTerritory;

class TownCustomerController extends Controller{
     use SalesTerritory;
     public function index(){
          $title = "Town wise Target vs Achivement";

          return view('WebView/Sales.town_cus_wise_target',['title' => $title]);
     }

     public function getTownCusWiseTarget(Request $request){

          $validation = Validator::make($request->all(),[
               'date_month'=>'required'
          ]);

          $user = Auth::user();

          $time = time();

          if(!$validation->fails())
               $time = strtotime($request->input('date_month')."-01");

            $products = SalesmanValidPart::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-01',$time))->whereDate('to_date','>=',date('Y-m-t',$time))->get();
            $chemists = SalesmanValidCustomer::where('u_id',$user->getKey())->whereDate('from_date','<=',date('Y-m-01',$time))->whereDate('to_date','>=',date('Y-m-t',$time))->get();

            $invoice = DB::table('sub_town as sr')
                    ->join('chemist as c','c.sub_twn_id','sr.sub_twn_id')
                    ->join('invoice_line as il','il.chemist_id','c.chemist_id')
                    ->join('product as p','p.product_id','il.product_id')
                    ->leftJoin('latest_price_informations AS pi',function($query){
                        $query->on('pi.product_id','=','p.product_id');
                        $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                            })
                    ->select([
                         'c.sub_twn_id',
                        //  'sr.sub_twn_id',
                         'c.chemist_code',
                         'c.chemist_name',
                         DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                    ])
                    ->whereIn('il.product_id',$products->pluck('product_id')->all())
                    ->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all())
                    ->whereDate('il.invoice_date','>=',date('Y-m-01',$time))
                    ->whereDate('il.invoice_date','<=',date('Y-m-t',$time))
                    ->whereNull('c.deleted_at')
                    ->whereNull('sr.deleted_at')
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pi.deleted_at')
                    ->groupBy('c.sub_twn_id');

            $return = DB::table('sub_town as sr')
                        ->join('chemist as c','c.sub_twn_id','sr.sub_twn_id')
                        ->join('return_lines as il','il.chemist_id','c.chemist_id')
                        ->join('product as p','p.product_id','il.product_id')
                        ->leftJoin('latest_price_informations AS pi',function($query){
                            $query->on('pi.product_id','=','p.product_id');
                            $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                                    })
                        ->select([
                            'c.sub_twn_id',
                            // 'sr.sub_twn_id',
                            'c.chemist_code',
                            'c.chemist_name',
                            DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                        ])
                        ->whereIn('il.product_id',$products->pluck('product_id')->all())
                        ->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all())
                        ->whereDate('il.invoice_date','>=',date('Y-m-01',$time))
                        ->whereDate('il.invoice_date','<=',date('Y-m-t',$time))
                        ->whereNull('c.deleted_at')
                        ->whereNull('sr.deleted_at')
                        ->whereNull('il.deleted_at')
                        ->whereNull('p.deleted_at')
                        ->whereNull('pi.deleted_at')
                        ->groupBy('c.sub_twn_id');

                    $invoices = $invoice->get();
                    $returns = $return->get();

                    $allachivements = $invoices->concat($returns);

                $route = SubTown::whereIn('sub_twn_id',$allachivements->pluck('sub_twn_id')->all());
                $results = $route->get();

                $routeAchi = 0;
                $balance = 0;
                $achi = 0;
                $results->transform(function($row) use($invoices,$returns,$routeAchi,$balance,$achi,$time){

                        $salesAchi = $invoices->where('sub_twn_id',$row->sub_twn_id)->sum('bdgt_value');
                        $returnAchi = $returns->where('sub_twn_id',$row->sub_twn_id)->sum('bdgt_value');

                        if(isset($salesAchi) && isset($returnAchi))
                            $routeAchi = $salesAchi - $returnAchi;

                        $chemists = Chemist::where('sub_twn_id',$row->sub_twn_id)->get();
                        $targets = SfaCustomerTarget::whereIn('sfa_cus_code',$chemists->pluck('chemist_id')->all())->where('sfa_year',date('Y',$time))->where('sfa_month',date('m',$time))->sum('sfa_target');

                        if((isset($targets) && isset($routeAchi) && ($targets > 0 && $routeAchi > 0)))
                            $balance = $targets - $routeAchi;

                        if((isset($targets) && isset($routeAchi) && ($targets > 0 && $routeAchi > 0)))
                            $achi = $routeAchi/$targets * 100;

                        $return['twn_name'] = isset($row->sub_twn_name)?$row->sub_twn_name:'';
                        // $return['chemist_code'] = $row->chemist_code?$row->chemist_code:'-';
                        // $return['chemist_name'] = $row->chemist_name?$row->chemist_name:'';
                        $return['target'] = isset($targets)?number_format($targets,2):0;
                        $return['achi'] = isset($routeAchi)?number_format($routeAchi,2):0;
                        $return['achi_%'] = isset($achi)?round($achi,2):0;
                        $return['balance'] = isset($balance)?number_format($balance,2):0;
                        return $return;
                });

          return view('WebView/Sales.town_cus_wise_target_search',['townCusTargets' => $results]);
    }
}

?>
