<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Principal;
use App\Exceptions\WebAPIException;
use App\Models\Area;
use App\Models\SalesmanValidCustomer;
use App\Models\SubTown;
use App\Models\Chemist;
use App\Models\Product;
use App\Models\SalesmanValidPart;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;
use App\Models\User;
use App\Traits\SalesTerritory;
use Illuminate\Support\Facades\Auth;

class PrincipalWiseTargetVsAchiReportController extends ReportController{
     protected $title = "Principle wise Target vs Achivement Report";

     use SalesTerritory;
     public function search(Request $request){
          $values = $request->input('values');

          $user = Auth::user();

          if($user){
               $userCode = substr($user->u_code,0,4);
               $area = Area::where('ar_code',$userCode)->first();
          }

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
                    ->whereNull('il.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('pr.deleted_at')
                    ->groupBy('pr.principal_id');


          if(isset($values['user'])){
               $products = SalesmanValidPart::where('u_id',$values['user']['value'])->whereDate('from_date','<=',$values['s_date'])->whereDate('to_date','>=',$values['e_date'])->get();
               $chemists = SalesmanValidCustomer::where('u_id',$values['user']['value'])->whereDate('from_date','<=',$values['s_date'])->whereDate('to_date','>=',$values['e_date'])->get();

               $invoice->whereIn('il.product_id',$products->pluck('product_id')->all());
               $invoice->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());

               $return->whereIn('il.product_id',$products->pluck('product_id')->all());
               $return->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());
          }


          if(isset($values['prin_id'])){
               $invoice->where('pr.principal_id',$values['prin_id']['value']);
               $return->where('pr.principal_id',$values['prin_id']['value']);
          }

          if(isset($values['pro_id'])){
               $invoice->where('il.product_id',$values['pro_id']['value']);
               $return->where('il.product_id',$values['pro_id']['value']);
          }

          if(isset($values['s_date']) && isset($values['e_date'])){
               $invoice->whereDate('il.invoice_date','>=',$values['s_date']);
               $invoice->whereDate('il.invoice_date','<=',$values['e_date']);

               $return->whereDate('il.invoice_date','>=',$values['s_date']);
               $return->whereDate('il.invoice_date','<=',$values['e_date']);
          }

          if($user->getRoll() == config('shl.area_sales_manager_type')){
               if(isset($area->ar_code)){
                    $users = User::where('u_code','LIKE','%'.$area->ar_code.'%')->get();

                    $products = SalesmanValidPart::whereIn('u_id',$users->pluck('id')->all())->whereDate('from_date','<=',$values['s_date'])->whereDate('to_date','>=',$values['e_date'])->get();
                    $chemists = SalesmanValidCustomer::whereIn('u_id',$users->pluck('id')->all())->whereDate('from_date','<=',$values['s_date'])->whereDate('to_date','>=',$values['e_date'])->get();

                    $invoice->whereIn('il.product_id',$products->pluck('product_id')->all());
                    $invoice->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());

                    $return->whereIn('il.product_id',$products->pluck('product_id')->all());
                    $return->whereIn('il.chemist_id',$chemists->pluck('chemist_id')->all());
               }
          }

          $grandproductnet = DB::table(DB::raw("({$invoice->toSql()}) as sub"))
            ->mergeBindings(get_class($invoice) == 'Illuminate\Database\Eloquent\Builder' ? $invoice->getQuery() : $invoice)->sum(DB::raw('bdgt_value'));

        $grandproductnet2 = DB::table(DB::raw("({$return->toSql()}) as sub"))
            ->mergeBindings(get_class($return) == 'Illuminate\Database\Eloquent\Builder' ? $return->getQuery() : $return)->sum(DB::raw('bdgt_value'));

        $grandproduct_achive=$grandproductnet-$grandproductnet2;


          $invoices = $invoice->get();
          $returns = $return->get();

          $allachivements = $invoices->concat($returns);

          $principal = Principal::whereIn('principal_id',$allachivements->pluck('principal_id')->all());
          $count = $this->paginateAndCount($principal,$request,'principal_id');

          $results = $principal->get();

          // $target = SfaTarget::where('u_id',$values['user']['value'])
          //      ->where('trg_year',date('Y',strtotime($values['month'])))
          //      ->where('trg_month',date('m',strtotime($values['month'])))
          //      ->latest()
          //      ->first();

          $target_product = SfaTargetProduct::with('product','product.principal')->whereDate('created_at','>=',$values['s_date'])->whereDate('created_at','<=',$values['e_date'])->get();

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
               $return['target_new'] = isset($target_pro)?$target_pro:0;
               $return['achi'] = isset($prinAchi)?number_format($prinAchi,2):0;
               $return['achi_new'] = isset($prinAchi)?$prinAchi:0;
               $return['achi_%'] = isset($achi_)?number_format($achi_,2):0;
               $return['balance'] = isset($balance)?number_format($balance,2):0;
               $return['balance_new'] = isset($balance)?$balance:0;

               return $return;
          });

          $row = [
               'special' => true,
               'principal' =>'Total',
               'target' => number_format($results->sum('target_new'),2),
               'achi' => number_format($results->sum('achi_new'),2),
               'ach_%' => NULL,
               'balance' => number_format($results->sum('balance_new'),2),
          ];

          $rownew = [
            'special' => true,
            'principal' =>'Grand Total',
            'target' => NULL,
            'achi' => number_format($grandproduct_achive,2),
            'ach_%' => NULL,
            'balance' => NULL,
       ];


          $results->push($row);
          $results->push($rownew);

          return[
               'results' => $results,
               'count' => $count
          ];
     }

     public function setColumns(ColumnController $columnController, Request $request){

          $columnController->text('principal')->setLabel('Principal');
          $columnController->number('target')->setLabel('Target');
          $columnController->number('achi')->setLabel('Achivement');
          $columnController->number('achi_%')->setLabel('%');
          $columnController->number('balance')->setLabel('Balance');

     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product')->setValidations('');
          $inputController->ajax_dropdown('prin_id')->setLabel('Principal')->setLink('principal')->setValidations('');
          $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id'=>'10'])->setValidations('');
          $inputController->date('s_date')->setLabel('From');
          $inputController->date('e_date')->setLabel('To');

          $inputController->setStructure([
               ['user','prin_id','pro_id'],['s_date','e_date']
          ]);
     }
}
?>
