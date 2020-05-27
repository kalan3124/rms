<?php

namespace App\Http\Controllers\Web\Reports\Distributor\Allocations;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Exceptions\WebAPIException;
use App\Models\DistributorSrCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DsrCustomerAllocationReportController extends ReportController
{
    protected $title = "DSR Customer Allocation Report";

    public function search($request)
    {
        $values = $request->input('values', []);

        $query = DistributorSrCustomer::with('user', 'distributorCustomer');

        if (isset($values['dsr_id'])) {
            $query->where('u_id', $values['dsr_id']['value']);
        }

        if (isset($values['cus_id'])) {
            $query->where('dc_id', $values['cus_id']['value']);
        }

        $count = $this->paginateAndCount($query, $request, 'u_id');

        $results = $query->get();

        $formatedResults = [];

        $u_code_num = "";



        foreach ($results as $key => $value) {

            //  $users = User::where('u_tp_id', 14)->where('u_code', 'LIKE', '%' . $value->u_code . '%')->get();

            $row = [];
            $counts = $results->where('u_id', $value->u_id)->count();


            if ($u_code_num != $value->user->u_code) {
                $row['dsr_code'] = $value->user->u_code;
                $row['dsr_code_rowspan'] = $counts;
                $row['dsr_name'] = $value->user->name;
                 $row['dsr_name_rowspan'] = $counts;

            } else {
                $row['dsr_code'] = null;
                $row['dsr_code_rowspan'] = 0;
                $row['dsr_name'] = null;
                $row['dsr_name_rowspan'] = 0;
            }


            $row['dsr_code'] = $value->user->u_code;
            $row['dsr_name'] = $value->user->name;
            $row['cus_code'] = $value->distributorCustomer->dc_code;
            $row['cus_name'] = $value->distributorCustomer->dc_name;
             $u_code_num = $value->user->u_code;

            $formatedResults[]=$row;
        }

        $results = $formatedResults;

        // $results->transform(function ($val) {
        //     return [

        //         'dsr_code' => $val->user->u_code,
        //         'dsr_name' => $val->user->name,
        //         'cus_code' => $val->distributorCustomer->dc_code,
        //         'cus_name' => $val->distributorCustomer->dc_name
        //     ];
        // });

        // $results->push($formatedResults);

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('dsr_code')->setLabel('DSR Code');
        $columnController->text('dsr_name')->setLabel('DSR Name');
        $columnController->text('cus_code')->setLabel('Customer Code');
        $columnController->text('cus_name')->setLabel('Customer Name');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('cus_id')->setLabel('Customer')->setLink('distributor_customer')->setValidations('');
        $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{dis_id}'])->setValidations('');
        $inputController->setStructure([['dsr_id', 'cus_id']]);
    }
}
