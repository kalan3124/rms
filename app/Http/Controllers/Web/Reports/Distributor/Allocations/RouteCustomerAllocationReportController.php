<?php

namespace App\Http\Controllers\Web\Reports\Distributor\Allocations;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Exceptions\WebAPIException;
use App\Form\DistributorCustomerClass;
use App\Models\DistributorCustomer;
use App\Models\DistributorSrCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteCustomerAllocationReportController extends ReportController {
     protected $title = "Route Customer Allocation Report";

    public function search($request){
          $values = $request->input('values',[]);

          $query = DistributorCustomer::with('route');
          $query->whereNotNull('route_id');

          if(isset($values['cus_id'])){
               $query->where('dc_id',$values['cus_id']['value']);
          }

          if(isset($values['route_id'])){
               $query->where('route_id',$values['route_id']['value']);
          }

          $count = $this->paginateAndCount($query,$request,'dc_id');
          $results = $query->get();

          $formatedResults = [];

          $u_code_num = "";

        //   foreach ($results as $key => $value) {

        //       $row = [];
        //       $counts = $results->where('route_id', $value->route_id)->count();

        //       if ($u_code_num != $value->route->route_id) {
        //           $row['route_code'] =isset($value->route->route_code)?$value->route->route_code:null;
        //           $row['route_code_rowspan'] = $counts;
        //           $row['route_name'] = isset($value->route->route_name)?$value->route->route_name:null;
        //           $row['route_name_rowspan'] = $counts;
        //       } else {
        //           $row['route_code'] = null;
        //           $row['route_code_rowspan'] = 0;
        //           $row['route_name'] = null;
        //           $row['route_name_rowspan'] = 0;
        //       }

        //       $row['route_code'] = isset($value->route->route_code)?$value->route->route_code:null;
        //       $row['route_name'] = isset($value->route->route_name)?$value->route->route_name:null;
        //       $row['cus_code'] = isset($value->dc_code)?$value->dc_code:NULL;
        //       $row['cus_name'] = isset($value->dc_name)?$value->dc_name:NULL;
        //       $u_code_num = $value->route->route_id;

        //       $formatedResults[] = $row;
        //   }

        //   $results = $formatedResults;



          $results->transform(function($val){
               return[
                    'route_code' => isset($val->route->route_code)?$val->route->route_code:null,
                    'route_name' => isset($val->route->route_name)?$val->route->route_name:null,
                    'cus_code' => $val->dc_code,
                    'cus_name' => $val->dc_name
               ];
          });

          return[
               'results' => $results,
               'count' => $count
          ];
    }

     public function setColumns(ColumnController $columnController, Request $request){
          $columnController->text('route_code')->setLabel('Route Code');
          $columnController->text('route_name')->setLabel('Route Name');
          $columnController->text('cus_code')->setLabel('Customer Code');
          $columnController->text('cus_name')->setLabel('Customer Name');
     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown('cus_id')->setLabel('Customer')->setLink('distributor_customer')->setValidations('');
          $inputController->ajax_dropdown('route_id')->setLabel('Route')->setLink('route')->setValidations('');
          $inputController->setStructure([['route_id','cus_id']]);
     }
}
