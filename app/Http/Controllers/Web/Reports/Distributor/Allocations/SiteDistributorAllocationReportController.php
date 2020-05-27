<?php

namespace App\Http\Controllers\Web\Reports\Distributor\Allocations;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Exceptions\WebAPIException;
use App\Models\DistributorSite;
use App\Models\DistributorSrCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class SiteDistributorAllocationReportController extends ReportController
{
    protected $title = "Site Distributor Allocation Report";

    public function search($request)
    {
        $values = $request->input('values', []);

        $query = DistributorSite::with('site', 'distributor');

        if (isset($values['site_id'])) {
            $query->where('site_id', $values['site_id']['value']);
        }

        if (isset($values['dis_id'])) {
            $query->where('dis_id', $values['dis_id']['value']);
        }

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $query->where('dis_id', $user->getKey());
        }

        $count = $this->paginateAndCount($query, $request, 'site_id');
        $results = $query->get();


        $formatedResults = [];

        $u_code_num = "";

        foreach ($results as $key => $value) {

            $row = [];
            $counts = $results->where('site_id', $value->site_id)->count();

            if ($u_code_num != $value->site->site_id) {
                $row['site_code'] = $value->site->site_code;
                $row['site_code_rowspan'] = $counts;
                $row['site_name'] = $value->site->site_code;
                $row['site_name_rowspan'] = $counts;
            } else {
                $row['site_code'] = null;
                $row['site_code_rowspan'] = 0;
                $row['site_name'] = null;
                $row['site_name_rowspan'] = 0;
            }
            $row['site_code'] = $value->site->site_code;
            $row['site_name'] = $value->site->site_name;
            $row['dis_code'] = $value->distributor->u_code;
            $row['dis_name'] = $value->distributor->name;
            $u_code_num = $value->site->site_id;

            $formatedResults[] = $row;
        }

        $results = $formatedResults;

        //   $results->transform(function($val){
        //        return[
        //             'site_code' => $val->site->site_code,
        //             'site_name' => $val->site->site_name,
        //             'dis_code' => $val->distributor->u_code,
        //             'dis_name' => $val->distributor->name
        //        ];
        //   });

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('site_code')->setLabel('Site Code');
        $columnController->text('site_name')->setLabel('Site Name');
        $columnController->text('dis_code')->setLabel('Distributor Code');
        $columnController->text('dis_name')->setLabel('Distributor Name');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')])->setValidations('');
        $inputController->ajax_dropdown('site_id')->setLabel('Site')->setLink('site')->setValidations('');
        $inputController->setStructure([['site_id', 'dis_id']]);
    }
}
