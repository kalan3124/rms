<?php

namespace App\Http\Controllers\Web\Reports\Distributor\Allocations;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Exceptions\WebAPIException;
use App\Models\DistributorSrCustomer;
use App\Models\DsrProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DsrProductAllocationReportController extends ReportController {
     protected $title = "DSR Product Allocation Report";

    public function search($request){
          $values = $request->input('values',[]);

          $query = DsrProduct::with('product','distributor');

          if(isset($values['dsr_id'])){
               $query->where('dsr_id',$values['dsr_id']['value']);
          }

          if(isset($values['pro_id'])){
               $query->where('product_id',$values['pro_id']['value']);
          }

          $count = $this->paginateAndCount($query,$request,'dsr_id');
          $results = $query->get();

          $formatedResults = [];

          $u_code_num = "";

          foreach ($results as $key => $value) {

            $row = [];
            $counts = $results->where('dsr_id', $value->dsr_id)->count();

            if ($u_code_num != $value->distributor->u_code) {
                $row['dsr_code'] = $value->distributor->u_code;
                $row['dsr_code_rowspan'] = $counts;
                $row['dsr_name'] = $value->distributor->name;
                $row['dsr_name_rowspan'] = $counts;
            } else {
                $row['dsr_code'] = null;
                $row['dsr_code_rowspan'] = 0;
                $row['dsr_name'] = null;
                $row['dsr_name_rowspan'] = 0;
            }
            $row['dsr_code'] = $value->distributor->u_code;
            $row['dsr_name'] = $value->distributor->name;
            $row['pro_code'] = $value->product->product_code;
            $row['pro_name'] = $value->product->product_name;
            $u_code_num = $value->distributor->u_code;

            $formatedResults[] = $row;
        }

        $results = $formatedResults;

        //   $results->transform(function($val){
        //        return[
        //             'dsr_code' => $val->distributor->u_code,
        //             'dsr_name' => $val->distributor->name,
        //             'pro_code' => $val->product->product_code,
        //             'pro_name' => $val->product->product_name
        //        ];
        //   });

          return[
               'results' => $results,
               'count' => $count
          ];
    }

     public function setColumns(ColumnController $columnController, Request $request){
          $columnController->text('dsr_code')->setLabel('DSR Code');
          $columnController->text('dsr_name')->setLabel('DSR Name');
          $columnController->text('pro_code')->setLabel('Product Code');
          $columnController->text('pro_name')->setLabel('Product Name');
     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product')->setValidations('');
          $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_sales_rep_type'),'dis_id'=>'{dis_id}'])->setValidations('');
          $inputController->setStructure([['dsr_id','pro_id']]);
     }
}
