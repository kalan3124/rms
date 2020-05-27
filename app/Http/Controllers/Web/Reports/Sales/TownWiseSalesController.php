<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\WebAPIException;
use App\Exports\TownWiseSalesReport;
use Illuminate\Support\Facades\DB;
use App\Models\SubTown;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Chemist;
use App\Models\Route;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaCustomerTarget;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Form\Inputs\InputController;

class TownWiseSalesController extends ReportController {

    public function search(Request $request){
        $values = $request->input('values');

        if(!isset($values['user']))
            throw new WebAPIException("User  field is required!");

        $townWise = [];
        $routeWise = [];
        $customerWise = [];

        $townWise =  $this->townWiseSales($request);
        $routeWise =  $this->routeWiseSales($request);
        $customerWise =  $this->customerWiseSales($request);

        return[
            'results1' => $townWise,
            'results2' => $routeWise,
            'results3' => $customerWise,
            'count' => 0
        ];
    }

    public function customerWiseSales($request){
        $values = $request->input('values');

        $invoice = DB::table('chemist as c')
                    ->join('invoice_line as il','il.chemist_id','c.chemist_id')
                    ->join('product as p','p.product_id','il.product_id')
                    ->leftJoin('latest_price_informations AS pi',function($query){
                        $query->on('pi.product_id','=','p.product_id');
                        $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                            })
                    ->select([
                         'c.chemist_id',
                         DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                    ])
                    ->whereNull('c.deleted_at')
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pi.deleted_at')
                    ->groupBy('c.chemist_id');

          $return = DB::table('chemist as c')
                    ->join('return_lines as il','il.chemist_id','c.chemist_id')
                    ->join('product as p','p.product_id','il.product_id')
                    ->leftJoin('latest_price_informations AS pi',function($query){
                        $query->on('pi.product_id','=','p.product_id');
                        $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                            })
                    ->select([
                         'c.chemist_id',
                         DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                    ])
                    ->whereNull('c.deleted_at')
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pi.deleted_at')
                    ->groupBy('c.chemist_id');

               if(isset($values['user'])){
                    $products = SalesmanValidPart::where('u_id',$values['user']['value'])->whereDate('from_date','<=',date('Y-m-01',strtotime($values['month'])))->whereDate('to_date','>=',date('Y-m-t',strtotime($values['month'])))->get();
                    $chemists = SalesmanValidCustomer::where('u_id',$values['user']['value'])->whereDate('from_date','<=',date('Y-m-01',strtotime($values['month'])))->whereDate('to_date','>=',date('Y-m-t',strtotime($values['month'])))->get();

                    $invoice->whereIn('il.product_id',$products->pluck('product_id')->all());
                    $invoice->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());

                    $return->whereIn('il.product_id',$products->pluck('product_id')->all());
                    $return->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());
               }

               if(isset($values['chem_id'])){
                    $invoice->where('il.chemist_id',$values['chem_id']['value']);
                    $return->where('il.chemist_id',$values['chem_id']['value']);
               }

               if(isset($values['month'])){
                    $invoice->whereDate('il.invoice_date','>=',date('Y-m-01',strtotime($values['month'])));
                    $invoice->whereDate('il.invoice_date','<=',date('Y-m-t',strtotime($values['month'])));

                    $return->whereDate('il.invoice_date','>=',date('Y-m-01',strtotime($values['month'])));
                    $return->whereDate('il.invoice_date','<=',date('Y-m-t',strtotime($values['month'])));
               }

               $invoices = $invoice->get();
               $returns = $return->get();

               $allachivements = $invoices->concat($returns);

               $chemist = Chemist::whereIn('chemist_id',$allachivements->pluck('chemist_id')->all());
               $results = $chemist->get();

               $chemistAchi = 0;
               $balance = 0;
               $achi = 0;
               $results->transform(function($row) use($invoices,$returns,$values,$chemistAchi,$balance,$achi){

                    $salesAchi = $invoices->where('chemist_id',$row->chemist_id)->sum('bdgt_value');
                    $returnAchi = $returns->where('chemist_id',$row->chemist_id)->sum('bdgt_value');

                    if(isset($salesAchi) && isset($returnAchi))
                         $chemistAchi = $salesAchi - $returnAchi;

                    $targets = SfaCustomerTarget::where('sfa_cus_code',$row->chemist_id)->where('sfa_year',date('Y',strtotime($values['month'])))->where('sfa_month',date('m',strtotime($values['month'])))->first();

                    if((isset($targets->sfa_target) && isset($chemistAchi) && ($targets->sfa_target > 0 && $chemistAchi > 0)))
                         $balance = $targets->sfa_target - $chemistAchi;

                    if((isset($targets->sfa_target) && isset($chemistAchi) && ($targets->sfa_target > 0 && $chemistAchi > 0)))
                         $achi = $chemistAchi/$targets->sfa_target * 100;

                    $return['chemist'] = isset($row->chemist_code)?$row->chemist_code:'-';
                    $return['chemist_name'] = isset($row->chemist_name)?$row->chemist_name:'-';
                    $return['target'] = isset($targets->sfa_target)?number_format($targets->sfa_target,2):0;
                    $return['target_new'] = isset($targets->sfa_target)?$targets->sfa_target:0;
                    $return['achi'] = isset($chemistAchi)?number_format($chemistAchi,2):0;
                    $return['achi_new'] = isset($chemistAchi)?$chemistAchi:0;
                    $return['precentage'] = isset($achi)?round($achi,2):0;
                    $return['balance'] = isset($balance)?number_format($balance,2):0;
                    $return['balance_new'] = isset($balance)?$balance:0;
                    return $return;
               });

               $row = [
                    'special' => true,
                    'chemist' => 'Total',
                    'chemist_name' => NULL,
                    'target' => number_format($results->sum('target_new'),2),
                    'achi' => number_format($results->sum('achi_new'),2),
                    'ach_%' => NULL,
                    'balance' => number_format($results->sum('balance_new'),2),
               ];

               $results->push($row);

        return $results;
    }

    public function townWiseSales($request){
        $values = $request->input('values');

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
                         'sr.sub_twn_id',
                         DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                    ])
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
                         'sr.sub_twn_id',
                         DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                    ])
                    ->whereNull('c.deleted_at')
                    ->whereNull('sr.deleted_at')
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pi.deleted_at')
                    ->groupBy('c.sub_twn_id');

                    if(isset($values['user'])){
                         $products = SalesmanValidPart::where('u_id',$values['user']['value'])->whereDate('from_date','<=',date('Y-m-01',strtotime($values['month'])))->whereDate('to_date','>=',date('Y-m-t',strtotime($values['month'])))->get();
                         $chemists = SalesmanValidCustomer::where('u_id',$values['user']['value'])->whereDate('from_date','<=',date('Y-m-01',strtotime($values['month'])))->whereDate('to_date','>=',date('Y-m-t',strtotime($values['month'])))->get();

                         $invoice->whereIn('il.product_id',$products->pluck('product_id')->all());
                         $invoice->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());

                         $return->whereIn('il.product_id',$products->pluck('product_id')->all());
                         $return->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());
                    }

                    if(isset($values['sub_town'])){
                         $invoice->where('c.sub_twn_id',$values['sub_town']['value']);
                         $return->where('c.sub_twn_id',$values['sub_town']['value']);
                    }

                    if(isset($values['chem_id'])){
                         $invoice->where('il.chemist_id',$values['chem_id']['value']);
                         $return->where('il.chemist_id',$values['chem_id']['value']);
                    }

                    if(isset($values['month'])){
                         $invoice->whereDate('il.invoice_date','>=',date('Y-m-01',strtotime($values['month'])));
                         $invoice->whereDate('il.invoice_date','<=',date('Y-m-t',strtotime($values['month'])));

                         $return->whereDate('il.invoice_date','>=',date('Y-m-01',strtotime($values['month'])));
                         $return->whereDate('il.invoice_date','<=',date('Y-m-t',strtotime($values['month'])));
                    }

               $invoices = $invoice->get();
               $returns = $return->get();

               $allachivements = $invoices->concat($returns);

               $route = SubTown::whereIn('sub_twn_id',$allachivements->pluck('sub_twn_id')->all());
               $results = $route->get();

               $routeAchi = 0;
               $balance = 0;
               $achi = 0;
               $results->transform(function($row) use($invoices,$returns,$values,$routeAchi,$balance,$achi){

                    $salesAchi = $invoices->where('sub_twn_id',$row->sub_twn_id)->sum('bdgt_value');
                    $returnAchi = $returns->where('sub_twn_id',$row->sub_twn_id)->sum('bdgt_value');

                    if(isset($salesAchi) && isset($returnAchi))
                         $routeAchi = $salesAchi - $returnAchi;

                    $chemists = Chemist::where('sub_twn_id',$row->sub_twn_id)->get();
                    $targets = SfaCustomerTarget::whereIn('sfa_cus_code',$chemists->pluck('chemist_id')->all())->where('sfa_year',date('Y',strtotime($values['month'])))->where('sfa_month',date('m',strtotime($values['month'])))->sum('sfa_target');

                    if((isset($targets) && isset($routeAchi) && ($targets > 0 && $routeAchi > 0)))
                         $balance = $targets - $routeAchi;

                    if((isset($targets) && isset($routeAchi) && ($targets > 0 && $routeAchi > 0)))
                         $achi = $routeAchi/$targets * 100;

                    $return['town_name'] = isset($row->sub_twn_name)?$row->sub_twn_name:'-';
                    $return['target'] = isset($targets)?number_format($targets,2):0;
                    $return['target_new'] = isset($targets)?$targets:0;
                    $return['achi'] = isset($routeAchi)?number_format($routeAchi,2):0;
                    $return['achi_new'] = isset($routeAchi)?$routeAchi:0;
                    $return['ach_pra'] = isset($achi)?round($achi,2):0;
                    $return['balance'] = isset($balance)?number_format($balance,2):0;
                    $return['balance_new'] = isset($balance)?$balance:0;
                    return $return;
               });

               $row = [
                    'special' => true,
                    'town_name' => 'Total',
                    'target' => number_format($results->sum('target_new'),2),
                    'achi' => number_format($results->sum('achi_new'),2),
                    'ach_%' => NULL,
                    'balance' => number_format($results->sum('balance_new'),2),
               ];

               $results->push($row);

        return $results;
    }

    public function routeWiseSales($request){
        $values = $request->input('values');

        $invoice = DB::table('sfa_route as sr')
                    ->join('chemist as c','c.route_id','sr.route_id')
                    ->join('invoice_line as il','il.chemist_id','c.chemist_id')
                    ->join('product as p','p.product_id','il.product_id')
                    ->join('area as ar','ar.ar_id','sr.ar_id')
                    ->leftJoin('latest_price_informations AS pi',function($query){
                        $query->on('pi.product_id','=','p.product_id');
                        $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                            })
                    ->select([
                         'c.route_id',
                         'sr.route_name',
                         DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                    ])
                    ->whereNull('c.deleted_at')
                    ->whereNull('sr.deleted_at')
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pi.deleted_at')
                    ->groupBy('c.route_id');

          $return = DB::table('sfa_route as sr')
                    ->join('chemist as c','c.route_id','sr.route_id')
                    ->join('return_lines as il','il.chemist_id','c.chemist_id')
                    ->join('product as p','p.product_id','il.product_id')
                    ->leftJoin('latest_price_informations AS pi',function($query){
                        $query->on('pi.product_id','=','p.product_id');
                        $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                            })
                    ->join('area as ar','ar.ar_id','sr.ar_id')
                    ->select([
                         'c.route_id',
                         'sr.route_name',
                         DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
                    ])
                    ->whereNull('c.deleted_at')
                    ->whereNull('sr.deleted_at')
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pi.deleted_at')
                    ->groupBy('c.route_id');

                    if(isset($values['user'])){
                         $products = SalesmanValidPart::where('u_id',$values['user']['value'])->whereDate('from_date','<=',date('Y-m-01',strtotime($values['month'])))->whereDate('to_date','>=',date('Y-m-t',strtotime($values['month'])))->get();
                         $chemists = SalesmanValidCustomer::where('u_id',$values['user']['value'])->whereDate('from_date','<=',date('Y-m-01',strtotime($values['month'])))->whereDate('to_date','>=',date('Y-m-t',strtotime($values['month'])))->get();

                         $invoice->whereIn('il.product_id',$products->pluck('product_id')->all());
                         $invoice->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());

                         $return->whereIn('il.product_id',$products->pluck('product_id')->all());
                         $return->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());
                    }

                    if(isset($values['route'])){
                         $invoice->where('c.route_id',$values['route']['value']);
                         $return->where('c.route_id',$values['route']['value']);
                    }

                    if(isset($values['chem_id'])){
                         $invoice->where('il.chemist_id',$values['chem_id']['value']);
                         $return->where('il.chemist_id',$values['chem_id']['value']);
                    }

                    if(isset($values['month'])){
                         $invoice->whereDate('il.invoice_date','>=',date('Y-m-01',strtotime($values['month'])));
                         $invoice->whereDate('il.invoice_date','<=',date('Y-m-t',strtotime($values['month'])));

                         $return->whereDate('il.invoice_date','>=',date('Y-m-01',strtotime($values['month'])));
                         $return->whereDate('il.invoice_date','<=',date('Y-m-t',strtotime($values['month'])));
                    }

               $invoices = $invoice->get();
               $returns = $return->get();

               $allachivements = $invoices->concat($returns);

               $route = Route::whereIn('route_id',$allachivements->pluck('route_id')->all());
               $results = $route->get();

               $routeAchi = 0;
               $balance = 0;
               $achi = 0;
               $results->transform(function($row) use($invoices,$returns,$values,$routeAchi,$balance,$achi){

                    $salesAchi = $invoices->where('route_id',$row->route_id)->sum('bdgt_value');
                    $returnAchi = $returns->where('route_id',$row->route_id)->sum('bdgt_value');

                    if(isset($salesAchi) && isset($returnAchi))
                         $routeAchi = $salesAchi - $returnAchi;

                    $chemists = Chemist::where('route_id',$row->route_id)->get();
                    $targets = SfaCustomerTarget::whereIn('sfa_cus_code',$chemists->pluck('chemist_id')->all())->where('sfa_year',date('Y',strtotime($values['month'])))->where('sfa_month',date('m',strtotime($values['month'])))->sum('sfa_target');

                    if((isset($targets) && isset($routeAchi) && ($targets > 0 && $routeAchi > 0)))
                         $balance = $targets - $routeAchi;

                    if((isset($targets) && isset($routeAchi) && ($targets > 0 && $routeAchi > 0)))
                         $achi = $routeAchi/$targets * 100;

                    $return['route'] = isset($row->route_name)?$row->route_name:'';
                    $return['target'] = isset($targets)?number_format($targets,2):0;
                    $return['target_new'] = isset($targets)?$targets:0;
                    $return['achi'] = isset($routeAchi)?number_format($routeAchi,2):0;
                    $return['achi_new'] = isset($routeAchi)?$routeAchi:0;
                    $return['ach_pra'] = isset($achi)?round($achi,2):0;
                    $return['balance'] = isset($balance)?number_format($balance,2):0;
                    $return['balance_new'] = isset($balance)?$balance:0;
                    return $return;
               });

               $row = [
                    'special' => true,
                    'route' => 'Total',
                    'target' => number_format($results->sum('target_new'),2),
                    'achi' => number_format($results->sum('achi_new'),2),
                    'ach_%' => NULL,
                    'balance' => number_format($results->sum('balance_new'),2),
               ];

               $results->push($row);

        return $results;
    }

    public function printExcel(Request $request){
          $values = $request->input('values');
          $values = $request->input('values');
          $data = [];

          if(!isset($values['user']))
               throw new WebAPIException("User  field is required!");

          $townWise = [];
          $routeWise = [];
          $customerWise = [];

          $routeWise =  $this->routeWiseSales($request);
          $townWise =  $this->townWiseSales($request);
          $customerWise =  $this->customerWiseSales($request);

          $data['results1'] = $routeWise;
          $data['results2'] = $townWise;
          $data['results3'] = $customerWise;

          $user = Auth::user();
          $userId = $user->getKey();

          if($values['user']['value']){
               $user = User::find($values['user']['value']);
          }

          $inputNames = [
            'user' => 'User',
            'route' => 'Route',
            'sub_town' => 'Sub Town',
            'chem_id' => 'Chemist',
            'month' => 'Month'
          ];

          $data['searchTerms'] = [];

          if(count($values)>0){
               foreach($inputNames as $name=>$input){
                    if(isset($values[$name]) && isset($values[$name]['value'])){
                         $data['searchTerms'][] = ['label'=>$input,'value'=>$values[$name]['label']];
                    }
               }
          }

          $userCode = str_replace([' ','/'.'\\','.'],'_',$user->u_code);
          $userName = str_replace([' ','/'.'\\','.'],'_',$user->name);

          $time = time();

          $data['link'] = url('/storage/xlsx/'.$userId.'/'.$userCode.'_'.$userName.'_'.$time.'.xlsx');

          Excel::store(new TownWiseSalesReport($data),'public/xlsx/'.$userId.'/'.$userCode.'_'.$userName.'_'.$time.'.xlsx');

          return response()->json([
               'file'=>$userId.'/'.$userCode.'_'.$userName.'_'.$time.'.xlsx'
          ]);
    }
}
?>
