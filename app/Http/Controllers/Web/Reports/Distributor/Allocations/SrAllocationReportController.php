<?php
namespace App\Http\Controllers\Web\Reports\Distributor\Allocations;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Exceptions\WebAPIException;
use App\Models\DistributorSrCustomer;
use App\Models\DistributorSalesMan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class SrAllocationReportController extends ReportController {

    protected $title = "SR Allocation Report";

    public function search($request){

        $values = $request->input('values',[]);

        $query = DistributorSalesMan::with('distributor','distributorSalesRep');
        $query->groupBy('dis_id');

        if(isset($values['sr_id'])){
            $query->where('sr_id', $values['sr_id']['value']);
        }

        if(isset($values['dis_id'])){
            $query->where('dis_id', $values['dis_id']['value']);
        }

        $user = Auth::user();

          if($user->u_tp_id == config('shl.distributor_type')){
              $query->where('dis_id',$user->getKey());
          }


        $count = $this->paginateAndCount($query,$request,'sr_id');
        $results = $query->get();


        $formatedResults = [];

        $u_code_num = "";

        foreach ($results as $key => $value) {

            $row = [];
            $counts = $results->where('sr_id', $value->sr_id)->count();

            if ($u_code_num != $value->distributorSalesRep->u_code) {
                $row['sr_code'] = $value->distributorSalesRep->u_code;
                $row['sr_code_rowspan'] = $counts;
                $row['sr_name'] = $value->distributorSalesRep->name;
                $row['sr_name_rowspan'] = $counts;
            } else {
                $row['sr_code'] = null;
                $row['sr_code_rowspan'] = 0;
                $row['sr_name'] = null;
                $row['sr_name_rowspan'] = 0;
            }

            $row['sr_code'] = $value->distributorSalesRep->u_code;
            $row['sr_name'] = $value->distributorSalesRep->name;
            $row['dis_code'] = $value->distributor->u_code;
            $row['dis_name'] = $value->distributor->name;
            $u_code_num = $value->distributorSalesRep->u_code;

            $formatedResults[] = $row;
        }

        $results = $formatedResults;


        // $results->transform(function($val){
        //     return[
        //         'dis_code' => $val->distributor->u_code,
        //         'dis_name' => $val->distributor->name,
        //         'sr_code' => $val->distributorSalesRep->u_code,
        //         'sr_name' => $val->distributorSalesRep->name
        //     ];
        // });

        return[
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request){

        $columnController->text('sr_code')->setLabel('SR Code');
        $columnController->text('sr_name')->setLabel('SR Name');
        $columnController->text('dis_code')->setLabel('Distributor Code');
        $columnController->text('dis_name')->setLabel('Distributor Name');
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('sr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id'=>config('shl.sales_rep_type')])->setValidations('');
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_type')])->setValidations('');
        $inputController->setStructure([['sr_id','dis_id']]);
    }
}
