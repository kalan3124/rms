<?php

namespace App\Http\Controllers\Web\Reports\Distributor\Allocations;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Exceptions\WebAPIException;
use App\Models\DistributorSalesRep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class DsrDistributorAllocationReportController extends ReportController
{
    protected $title = "DSR Distributor Allocation Report";

    public function search($request)
    {
        $values = $request->input('values', []);

        // if (!isset($values['dsr_id'])) {
        //     throw new WebAPIException('Sales rep field is required');
        // }

        $query = DistributorSalesRep::with('distributor', 'distributorSalesRep');

        if (isset($values['dsr_id'])) {
            $query->where('sr_id', $values['dsr_id']['value']);
        }

        if (isset($values['dis_id'])) {
            $query->where('dis_id', $values['dis_id']['value']);
        }

        $user = Auth::user();

          if($user->u_tp_id == config('shl.distributor_type')){
              $query->where('dis_id',$user->getKey());
          }

        $count = $this->paginateAndCount($query, $request, 'sr_id');
        $results = $query->get();


        $formatedResults = [];

        $u_code_num = "";

        foreach ($results as $key => $value) {

            $row = [];
            $counts = $results->where('sr_id', $value->sr_id)->count();

            $u_code_num_num =isset($value->distributorSalesRep->u_code)?$value->distributorSalesRep->u_code:NULL;

            if ($u_code_num != $u_code_num_num ) {
                $row['dsr_code'] = isset($value->distributorSalesRep->u_code)?$value->distributorSalesRep->u_code:NULL;
                $row['dsr_code_rowspan'] = $counts;
                $row['dsr_name'] = isset($value->distributorSalesRep->name)?$value->distributorSalesRep->name:NULL;
                $row['dsr_name_rowspan'] = $counts;
            } else {
                $row['dsr_code'] = null;
                $row['dsr_code_rowspan'] = 0;
                $row['dsr_name'] = null;
                $row['dsr_name_rowspan'] = 0;
            }

            $row['dsr_code'] = isset($value->distributorSalesRep->u_code)?$value->distributorSalesRep->u_code:NULL;
            $row['dsr_name'] = isset($value->distributorSalesRep->name)?$value->distributorSalesRep->name:NULL;
            $row['dis_code'] = isset($value->distributor->u_code)?$value->distributor->u_code:NULL;
            $row['dis_name'] = isset($value->distributor->name)?$value->distributor->name:NULL;

            $u_code_num = $value->distributorSalesRep->u_code;


            $formatedResults[] = $row;
        }

        $results = $formatedResults;



        // $results->transform(function ($val) {
        //     return [

        //         'dis_code' => isset($val->distributor->u_code)?$val->distributor->u_code:NULL,
        //         'dis_name' => isset($val->distributor->name)?$val->distributor->name:NULL,
        //         'dsr_code' => isset($val->distributorSalesRep->u_code)?$val->distributorSalesRep->u_code:NULL,
        //         'dsr_name' => isset($val->distributorSalesRep->name)?$val->distributorSalesRep->name:NULL,

        //     ];
        // });

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('dsr_code')->setLabel('DSR Code');
        $columnController->text('dsr_name')->setLabel('DSR Name');
        $columnController->text('dis_code')->setLabel('Distributor Code');
        $columnController->text('dis_name')->setLabel('Distributor Name');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')])->setValidations('');
        $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{dis_id}'])->setValidations('');
        $inputController->setStructure([['dsr_id', 'dis_id']]);
    }
}
