<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class StockAjustmentReportController extends ReportController {
     protected $title = "Stock Adjustment Report";

    public function search($request){
          $values = $request->input('values',[]);

          $query = DB::table('stock_adjusment as sa')
          ->select([
               'sa.stk_adj_date as date',
               'p.product_code',
               'p.product_name',
               'sa.stk_adj_date',
               'pr.principal_name',
               'db.db_code',
               'db.db_expire',
               'db.db_price',
               'sap.stk_adj_qty as qty'
          ])
          ->join('stock_adjusment_product as sap','sap.stk_adj_id','sa.stk_adj_id')
          ->join('product as p','p.product_id','sap.product_id')
          ->join('principal as pr','pr.principal_id','p.principal_id')
          ->join('distributor_batches as db','db.db_id','sap.db_id')
          ->where('sa.stk_adj_date','>=',$values['s_date'])
          ->where('sa.stk_adj_date','<=',$values['e_date'])
          ->whereNull('sa.deleted_at')
          ->whereNull('p.deleted_at')
          ->whereNull('pr.deleted_at')
          ->whereNull('db.deleted_at')
          ->whereNull('sap.deleted_at')
          ->groupBy('p.product_id');

          if(isset($values['dis_id'])){
               $query->where('sa.dis_id',$values['dis_id']['value']);
          }
          if(isset($values['pro_id'])){
               $query->where('sap.product_id',$values['pro_id']['value']);
          }

          $user = Auth::user();

          if($user->u_tp_id == config('shl.distributor_type')){
              $query->where('sa.dis_id',$user->getKey());
          }

          $grandtotnew = DB::table(DB::raw("({$query->toSql()}) as sub"))
          ->mergeBindings(get_class($query)=='Illuminate\Database\Eloquent\Builder'?$query->getQuery():$query)->sum(DB::raw('db_price * qty'));


          $ajust = $query->get();


          $query = DB::table('write_off as wo')
          ->select([
               'wo.wo_date as date',
               'p.product_code',
               'p.product_name',
               'pr.principal_name',
               'db.db_code',
               'db.db_expire',
               'db.db_price',
               'wop.wo_qty as qty'
          ])
          ->join('write_off_product as wop','wop.wo_id','wo.wo_id')
          ->join('product as p','p.product_id','wop.product_id')
          ->join('principal as pr','pr.principal_id','p.principal_id')
          ->join('distributor_batches as db','db.db_id','wop.db_id')
          ->where('wo.wo_date','>=',$values['s_date'])
          ->where('wo.wo_date','<=',$values['e_date'])
          ->whereNull('wo.deleted_at')
          ->whereNull('p.deleted_at')
          ->whereNull('pr.deleted_at')
          ->whereNull('db.deleted_at')
          ->whereNull('wop.deleted_at')
          ->groupBy('p.product_id');



          if(isset($values['dis_id'])){
               $query->where('wo.dis_id',$values['dis_id']['value']);
          }

          if(isset($values['pro_id'])){
               $query->where('wop.product_id',$values['pro_id']['value']);
          }

          $user = Auth::user();

        if($user->u_tp_id == config('shl.distributor_type')){
            $query->where('wo.dis_id',$user->getKey());
        }

        $grandtot = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings(get_class($query)=='Illuminate\Database\Eloquent\Builder'?$query->getQuery():$query)->sum(DB::raw('db_price * qty'));


        $count = $this->paginateAndCount($query, $request, 'wo.dis_id');


        $write = $query->get();

          $results = $ajust->merge($write);

          $results->all();

          $results->transform(function($val,$key){
               return[
                    'date' => date('Y-m-d',strtotime($val->date)),
                    'agency' => $val->principal_name,
                    'code' => $val->product_code,
                    'pro_name' => $val->product_name,
                    'adju_by' => '-',
                    'reason' => '-',
                    'adju_qty' => $val->qty,
                    'adju_value' => number_format($val->qty * $val->db_price,2),
                    'adju_value_new' => $val->qty * $val->db_price,
                    'batch_no' => $val->db_code,
                    'exp_date' => $val->db_expire
               ];
          });

          $row = [
            'special' => true,
            'date' =>'Total',
            'agency' =>NULL,
            'code' =>NULL,
            'pro_name'=>NULL,
            'adju_by'=>NULL,
            'reason'=>NULL,
            'adju_qty' => $results->sum('adju_qty'),
            'adju_value' => number_format($results->sum('adju_value_new'),2),
            'batch_no'=>NULL,
            'exp_date'=>NULL,


       ];

       $rownew = [
        'special' => true,
        'date' =>'Grand Total',
        'agency' =>NULL,
        'code' =>NULL,
        'pro_name'=>NULL,
        'adju_by'=>NULL,
        'reason'=>NULL,
        'adju_qty' => NULL,
        'adju_value' => number_format($grandtotnew+$grandtot,2),
        'batch_no'=>NULL,
        'exp_date'=>NULL,


   ];

       $results->push($row);
       $results->push($rownew);

          return[
               'results' => $results,
               'count' => $count
          ];
    }

     public function setColumns(ColumnController $columnController, Request $request){
          $columnController->text('date')->setLabel('Date');
          $columnController->text('agency')->setLabel('Agecny');
          $columnController->text('code')->setLabel('Code');
          $columnController->text('pro_name')->setLabel('Product');
          $columnController->text('adju_by')->setLabel('Adjusted by');
          $columnController->text('reason')->setLabel('Reason');
          $columnController->number('adju_qty')->setLabel('Adjusted qty ');
          $columnController->number('adju_value')->setLabel('Stock adjustment value');
          $columnController->text('batch_no')->setLabel('Batch No');
          $columnController->text('exp_date')->setLabel('Exp Date');
     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_type')]);
          $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product');
          $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_sales_rep_type'),'dis_id'=>'{dis_id}']);
          $inputController->date('s_date')->setLabel('From');
          $inputController->date('e_date')->setLabel('To');
          $inputController->setStructure([['dis_id','pro_id'],['s_date','e_date']]);
     }
}
