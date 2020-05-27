<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\GoodReceivedNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class PrimaryOrderVsInvoiceReportController extends ReportController {
     protected $title = "Primary  Order Vs Invoices Report";

    public function search($request){
          $values = $request->input('values',[]);

          $query = DB::table('purchase_order AS po')
               ->join('purchase_order_lines as pol','pol.po_id','po.po_id')
               ->join('users AS u','u.id','po.dis_id')
               ->join('product as p','p.product_id','pol.product_id')
               ->join('principal as pr','pr.principal_id','p.principal_id')
               ->join('good_received_note AS grn','grn.po_id','=','po.po_id')
               ->leftJoin('good_received_note_line as grnl', function ($join) {
                    $join->on('grnl.grn_id', '=', 'grn.grn_id')
                         ->on('grnl.product_id', '=', 'pol.product_id');
               })
               ->select([
                    'po.sr_id',
                    'u.name',
                    'po.po_number',
                    'po.dis_id',
                    'p.product_id',
                    'p.product_name',
                    'p.product_code',
                    'pr.principal_name',
                    // 'pol.created_at',
                    'po.created_at',
                    'po.po_id',
                    'pol.pol_qty',
                    'pol.pol_amount',
                    'p.pack_size',
                    'grn.grn_no',
                    'grn.grn_date',
                    'grnl.grnl_qty',
                    'grnl.grnl_price'
               ])
               ->whereNull('po.deleted_at')
               ->whereNull('pol.deleted_at')
               ->whereNull('p.deleted_at')
               ->whereNull('u.deleted_at');
               // ->groupBy('pol.po_id');

               $results = $query->get();

               $grandgrntot = DB::table(DB::raw("({$query->toSql()}) as sub"))
               ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('grnl_price'));

               $grandinvtot = DB::table(DB::raw("({$query->toSql()}) as sub"))
               ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('pol_amount-grnl_price'));

               $grandpotot = DB::table(DB::raw("({$query->toSql()}) as sub"))
               ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('pol_amount'));


          if(isset($values['dsr_id'])){
               $query->where('po.sr_id',$values['dsr_id']['value']);
          }

          if(isset($values['dis_id'])){
               $query->where('po.dis_id',$values['dis_id']['value']);
          }

          if(isset($values['pro_id'])){
               $query->where('p.product_id',$values['pro_id']['value']);
          }

          if(isset($values['po_no'])){
               $query->where('po.po_number','LIKE','%'.$values['po_no'].'%');
          }

          if(isset($values['s_date']) && isset($values['e_date'])){
            //    $query->whereDate('po.created_at','>=',$values['s_date']);
            //    $query->whereDate('po.created_at','<=',$values['e_date']);

               $query->whereBetween( DB::raw( 'DATE(po.created_at)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
          }

          $user = Auth::user();

          if($user->u_tp_id == config('shl.distributor_type')){
              $query->where('po.dis_id',$user->getKey());
          }

          $count = $this->paginateAndCount($query,$request,'dis_id');

          $results = $query->get();

          $results->transform(function($val){
               $dis_name = User::where('id',$val->dis_id)->first();

               if(((isset($val->pol_qty) && isset($val->grnl_qty)) && $val->pol_qty != $val->grnl_qty) && ($val->pol_qty > 0 && $val->grnl_qty > 0))
                    $not_inv_qty = $val->pol_qty - $val->grnl_qty;

               if(((isset($val->pol_amount) && isset($val->grnl_price)) && $val->pol_amount !=$val->grnl_price) && ($val->pol_amount > 0 && $val->grnl_price > 0))
                    $not_inv_val = $val->pol_amount - ($val->grnl_price*$val->grnl_qty);

               return[
                    'dis_name' => $dis_name->name,
                    'agency_name' => $val->principal_name,
                    'pro_code' => $val->product_code.' - '.$val->product_id,
                    'pro_name' => $val->product_name,
                    'pack_size' => $val->pack_size,
                    'po_date' => date('Y-m-d',strtotime($val->created_at)),
                    'po_no' => $val->po_number,
                    'po_qty' => $val->pol_qty,
                    'po_value' => isset($val->pol_amount)?number_format($val->pol_amount,2):0,
                    'po_value_new' => isset($val->pol_amount)? $val->pol_amount:0,
                    'ifs_inv_no' => $val->grn_no,
                    'ifs_inv_date' => date('Y-m-d',strtotime($val->grn_date)),
                    'ifs_inv_qty' => isset($val->grnl_qty)?$val->grnl_qty:0,
                    'ifs_inv_value' => isset($val->grnl_price) && isset($val->grnl_qty)?number_format($val->grnl_price*$val->grnl_qty,2):0,
                    'grn_no' => $val->grn_no,
                    'grn_date' => date('Y-m-d',strtotime($val->grn_date)),
                    'grn_qty' => isset($val->grnl_qty)?$val->grnl_qty:0,
                    'grn_value' => isset($val->grnl_price) && isset($val->grnl_qty)?number_format($val->grnl_price*$val->grnl_qty,2):0,
                    'grn_value_new' => isset($val->grnl_price)*$val->grnl_qty?$val->grnl_price*$val->grnl_qty:0,
                    'po_not_inv_qty' => isset($not_inv_qty)?$not_inv_qty:0,
                    'po_not_inv_value' => isset($not_inv_val)?number_format($not_inv_val,2):0,
                    'po_not_inv_value_new' => isset($not_inv_val)?$not_inv_val:0
               ];
          });

          $row = [
               'special' => true,
               'dis_name' =>'Total',
               'agency_name' =>NULL,
               'pro_code' =>NULL,
               'pro_name' =>NULL,
               'pack_size' =>NULL,
               'po_date' =>NULL,
               'po_no'=>NULL,
               'po_qty' => $results->sum('po_qty'),
               'po_value' => number_format($results->sum('po_value_new'),2),
               'ifs_inv_no'=>NULL,
               'ifs_inv_date'=>NULL,
               'ifs_inv_qty'=>NULL,
               'ifs_inv_value'=>NULL,
               'grn_no'=>NULL,
               'grn_date'=>NULL,
               'grn_qty' => $results->sum('grn_qty'),
               'grn_value' => number_format($results->sum('grn_value_new'),2),
               'po_not_inv_qty' => $results->sum('po_not_inv_qty'),
               'po_not_inv_value' => number_format($results->sum('po_not_inv_value_new'),2)
          ];
          $rownew = [
            'special' => true,
            'dis_name' =>'Grand Total',
            'agency_name' =>NULL,
            'pro_code' =>NULL,
            'pro_name' =>NULL,
            'pack_size' =>NULL,
            'po_date' =>NULL,
            'po_no'=>NULL,
            'po_qty' => NULL,
            'po_value' => number_format($grandpotot,2),
            'ifs_inv_no'=>NULL,
            'ifs_inv_date'=>NULL,
            'ifs_inv_qty'=>NULL,
            'ifs_inv_value'=>NULL,
            'grn_no'=>NULL,
            'grn_date'=>NULL,
            'grn_qty' => NULL,
            'grn_value' => number_format($grandgrntot,2),
            'po_not_inv_qty' => NULL,
            'po_not_inv_value' => number_format($grandinvtot,2)
          ];


          $results->push($row);
          $results->push($rownew);

          return[
               'results' => $results,
               'count' => $count
          ];
    }

     public function setColumns(ColumnController $columnController, Request $request){
          $values = $request->input('values',[]);

          $columnController->text('dis_name')->setLabel('Distributor Name');
          $columnController->text('agency_name')->setLabel('Agency Name');
          $columnController->text('pro_code')->setLabel('Product Code');
          $columnController->text('pro_name')->setLabel('Product Name');
          $columnController->text('pack_size')->setLabel('Pack Size');
          $columnController->text('po_date')->setLabel('PO Date');
          $columnController->text('po_no')->setLabel('PO No');
          $columnController->number('po_qty')->setLabel('PO Qty');
          $columnController->number('po_value')->setLabel('PO Value');
          $columnController->text('ifs_inv_no')->setLabel('IFS Invoice Number');
          $columnController->text('ifs_inv_date')->setLabel('IFS Invoice Date');
          $columnController->number('ifs_inv_qty')->setLabel('IFS Invoiced Qty');
          $columnController->number('ifs_inv_value')->setLabel('IFS Invoiced Value');
          $columnController->text('grn_no')->setLabel('GRN  No');
          $columnController->text('grn_date')->setLabel('GRN  Date');
          $columnController->number('grn_qty')->setLabel('GRN  Qty');
          $columnController->number('grn_value')->setLabel('GRN  Value');
          $columnController->number('po_not_inv_qty')->setLabel('PO Not Invoiced  Qty');
          $columnController->number('po_not_inv_value')->setLabel('PO Not Invoiced  Value');
     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_type')]);
          $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_sales_rep_type'),'dis_id'=>'{dis_id}']);
          $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product')->setValidations('');
          $inputController->text('po_no')->setLabel('Po Number')->setValidations('');
          $inputController->date('s_date')->setLabel('From');
          $inputController->date('e_date')->setLabel('To');
          $inputController->setStructure([['dsr_id','dis_id','pro_id'],['po_no','s_date','e_date']]);
     }
}
