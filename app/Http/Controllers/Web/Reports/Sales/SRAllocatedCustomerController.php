<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Http\Controllers\Web\Reports\ReportController;
use App\Form\Columns\ColumnController;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SRAllocatedCustomerController extends ReportController{

    protected $title = "SR Allocated Chemists";

    protected $defaultSortColumn = 'user_code';

    public function search($request){

        $user = Auth::user();

        if($user){
            $userCode = substr($user->u_code,0,4);

            $area = Area::where('ar_code',$userCode)->first();
        }

        $query = DB::table('salesman_valid_customer AS svc')
        ->join('chemist AS c','c.chemist_id','svc.chemist_id')
        ->join('users AS u','u.id','svc.u_id','u.u_code')
        ->select([
            'svc.salesman_code',
            'u.name',
            'c.chemist_code',
            'c.chemist_name',
            'svc.from_date',
            'svc.to_date',
            'c.image_url'
        ])
        ->whereNull('svc.deleted_at')
        ->whereNull('c.deleted_at')
        ->whereNull('u.deleted_at');

        if($request->has('values.user.value')){
            $query->where('u.id',$request->input('values.user.value'));
        }

        if($request->has('values.chemist.value')){
            $query->where('c.chemist_id',$request->input('values.chemist.value'));
        }

        if(!$request->has('values.user.value') && !$request->has('values.chemist.value')){
            if(isset($area->ar_code)){
                $query->where('u.u_code','LIKE','%'.$area->ar_code.'%');
            }
        }

        $sortMode = $request->input('sortMode')??'desc';
        $sortBy = 'svc.salesman_code';

        switch ($request->input('sortBy')) {
            case 'user_name':
                $sortBy='u.name';
                break;
            case 'chemist_code':
                $sortBy='c.chemist_code';
                break;
            case 'chemist_name':
                $sortBy='c.chemist_name';
                break;
            case 'from_date':
                $sortBy='svc.from_date';
                break;
            case 'to_date':
                $sortBy='svc.to_date';
                break;
            default:
                break;
        }

        $query->orderBy($sortBy,$sortMode);

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $result = $query->get();

        $formatedResults = [];

        $u_code_num = "";

        foreach ($result as $key => $value) {

            $row = [];
            $counts = $result->where('salesman_code', $value->salesman_code)->count();

            if ($u_code_num != $value->salesman_code) {
                $row['user_code'] = $value->salesman_code;
                $row['user_code_rowspan'] = $counts;
                $row['user_name'] = $value->name;
                $row['user_name_rowspan'] = $counts;
            } else {
                $row['user_code'] = null;
                $row['user_code_rowspan'] = 0;
                $row['user_name'] = null;
                $row['user_name_rowspan'] = 0;
            }

            $row['user_code'] = $value->salesman_code;
            $row['user_name'] = $value->name;
            $row['chemist_code'] = $value->chemist_code;
            $row['chemist_name'] = $value->chemist_name;
            $row['from_date'] = $value->from_date;
            $row['to_date'] = $value->to_date;
            $row['image'] = $value->image_url;

            $u_code_num = $value->salesman_code;

            $formatedResults[] = $row;
        }

        $result = $formatedResults;




        // $result->transform(function($row){
        //     return [
        //         'user_code'=>$row->salesman_code,
        //         'user_name'=>$row->name,
        //         'chemist_code'=>$row->chemist_code,
        //         'chemist_name'=>$row->chemist_name,
        //         'from_date'=>$row->from_date,
        //         'to_date'=>$row->to_date,
        //         'image' => $row->image_url
        //     ];
        // });

        return [
            'results'=>$result,
            'count'=>$count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('user_code')->setLabel('SR Code');
        $columnController->text('user_name')->setLabel('SR Name');
        $columnController->text('chemist_code')->setLabel('Chemist Code');
        $columnController->text('chemist_name')->setLabel('Chemist Name');
        $columnController->text("from_date")->setLabel("From Date");
        $columnController->text("to_date")->setLabel("To Date");
        $columnController->image('image')->setLabel("Image");
    }

    public function setInputs($inputController){
        $user = Auth::user();

        if($user){
            $userCode = substr($user->u_code,0,4);

            $area = Area::where('ar_code',$userCode)->first();
        }

        $inputController->ajax_dropdown('user')->setWhere(['u_tp_id'=>10])->setLabel("user")->setLink('user');
        $inputController->ajax_dropdown('chemist')->setLabel("Chemist")->setLink('chemist');
        $inputController->setStructure(['user','chemist']);
    }

}
