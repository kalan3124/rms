<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Http\Controllers\Web\Reports\ReportController;
use App\Form\Columns\ColumnController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class RouteCustomerController extends ReportController{

    protected $title = "Allocated Chemists For Route";

    protected $defaultSortColumn = 'route_code';

    public function search($request){

        $query = DB::table('chemist AS src')
        ->join('sfa_route AS sr','sr.route_id','src.route_id')
        ->join('chemist AS c','c.chemist_id','src.chemist_id')
        ->join('area AS a','a.ar_id','sr.ar_id')
        ->select([
            'sr.route_code',
            'sr.route_name',
            'a.ar_code',
            'a.ar_name',
            'c.chemist_code',
            'c.chemist_name'
        ])
        ->whereNull('src.deleted_at')
        ->whereNull('c.deleted_at')
        ->whereNull('a.deleted_at')
        ->whereNull('sr.deleted_at');

        if($request->has('values.route.value')){
            $query->where('src.route_id',$request->input('values.route.value'));
        }

        if($request->has('values.chemist.value')){
            $query->where('c.chemist_id',$request->input('values.chemist.value'));
        }

        if($request->has('values.area.value')){
            $query->where('sr.ar_id',$request->input('values.area.value'));
        }

        $sortMode = $request->input('sortMode')??'desc';
        $sortBy = 'sr.route_code';

        switch ($request->input('sortBy')) {
            case 'route_name':
                $sortBy='sr.route_name';
                break;
            case 'ar_code':
                $sortBy='a.ar_code';
                break;
            case 'ar_name':
                $sortBy='a.ar_name';
                break;
            case 'chemist_code':
                $sortBy='c.chemist_code';
                break;
            case 'chemist_name':
                $sortBy='c.chemist_name';
                break;
        }

        $query->orderBy($sortBy,$sortMode);

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $result = $query->get();


        $query->orderBy($sortBy,$sortMode);

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $result = $query->get();


        $formatedResults = [];

        $u_code_num = "";

        foreach ($result as $key => $value) {

            $row = [];
            $counts = $result->where('route_code', $value->route_code)->count();

            if ($u_code_num != $value->route_code) {
                $row['route_code'] = $value->route_code;
                $row['route_code_rowspan'] = $counts;
                $row['route_name'] = $value->route_name;
                $row['route_name_rowspan'] = $counts;
                $row['ar_code'] = $value->ar_code;
                $row['ar_code_rowspan'] = $counts;
                $row['ar_name'] = $value->ar_name;
                $row['ar_name_rowspan'] = $counts;
            } else {
                $row['route_code'] = null;
                $row['route_code_rowspan'] = 0;
                $row['route_name'] = null;
                $row['route_name_rowspan'] = 0;
                $row['ar_code'] = null;
                $row['ar_code_rowspan'] = 0;
                $row['ar_name'] = null;
                $row['ar_name_rowspan'] = 0;


            }

            $row['route_code'] = $value->route_code;
            $row['route_name'] = $value->route_name;
            $row['ar_code'] = $value->ar_code;
            $row['ar_name'] = $value->ar_name;
            $row['chemist_code'] = $value->chemist_code;
            $row['chemist_name'] = $value->chemist_name;

            // $row['to_date'] = $value->to_date;


            $u_code_num = $value->route_code;

            $formatedResults[] = $row;
        }

        $result = $formatedResults;


        // $result->transform(function($row){
        //     return [
        //         'route_code'=>$row->route_code,
        //         'route_name'=>$row->route_name,
        //         'ar_code'=>$row->ar_code,
        //         'ar_name'=>$row->ar_name,
        //         'chemist_code'=>$row->chemist_code,
        //         'chemist_name'=>$row->chemist_name
        //     ];
        // });

        return [
            'results'=>$result,
            'count'=>$count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('route_code')->setLabel('Route Code');
        $columnController->text('route_name')->setLabel('Route Name');
        $columnController->text('ar_code')->setLabel('Area Code');
        $columnController->text('ar_name')->setLabel('Area Name');
        $columnController->text('chemist_code')->setLabel('Chemist Code');
        $columnController->text('chemist_name')->setLabel('Chemist Name');
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('route')->setWhere(['ar_id'=>'{area}','route_type'=>0])->setLabel("Route")->setLink('route');
        $inputController->ajax_dropdown('chemist')->setWhere(['route_id'=>'{route}'])->setLabel("Chemist")->setLink('chemist');
        $inputController->ajax_dropdown('area')->setLabel("Area")->setLink('area');
        $inputController->setStructure(['area','route','chemist']);
    }
}
