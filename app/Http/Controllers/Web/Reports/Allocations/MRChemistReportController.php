<?php
namespace App\Http\Controllers\Web\Reports\Allocations;

use App\Http\Controllers\Web\Reports\ReportController;
use App\Form\Columns\ColumnController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class MRChemistReportController extends ReportController{

    protected $title = "MR Chemist List";

    protected $defaultSortColumn = 't.tm_id' ;

    public function search($request){

        switch ($request->input('sortBy')) {
            case 'team':
               $sortBy = 't.tm_id';
                break;
            case 'user_code':
                $sortBy = 'u.u_code';
                break;
            case 'user_name':
                $sortBy = 'u.name';
                break;
            case 'chemist_code':
                $sortBy = 'c.chemist_code';
                break;
            case 'chemist_name':
                $sortBy = 'c.chemist_name';
                break;
            default:
                $sortBy = 'uc.updated_at';
                break;
        }
        
        $query = DB::table('user_customer AS uc')
            ->select(['t.tm_name','t.tm_code','u.u_code','u.name','c.chemist_code','c.chemist_name','uc.updated_at'])
            ->join('chemist AS c','uc.chemist_id','=','c.chemist_id','inner')
            ->join('users AS u','u.id','=','uc.u_id')
            ->leftJoin('team_users AS tu','tu.u_id','=','uc.u_id')
            ->leftJoin('teams AS t',DB::raw('IF(tu.tm_id IS NULL,t.fm_id,t.tm_id)'),'=',DB::raw('IFNULL(tu.tm_id,uc.u_id)'))
            ->whereNull('c.deleted_at')
            ->whereNull('t.deleted_at')
            ->whereNull('tu.deleted_at')
            ->whereNull('uc.deleted_at');

        if($request->has('values.team.value')){
            $query->where('t.tm_id',$request->input('values.team.value'));
        }

        if($request->has('values.user.value')){
            $query->where('u.id',$request->input('values.user.value'));
        }

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $result = $query->get();

        $result->transform(function($row){
            return [
                'team_code'=>$row->tm_code,
                'team'=>$row->tm_name,
                'user_code'=>$row->u_code,
                'user_name'=> $row->name,
                'chemist_code'=>$row->chemist_code,
                'chemist_name'=>$row->chemist_name,
                'date'=>$row->updated_at
            ];
        });

        return [
            'results'=>$result,
            'count'=>$count
        ];
    }


    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('team')->setLabel("Team");
        $columnController->text('team_code')->setLabel("Team Code");
        $columnController->text('user_code')->setLabel('MR Code');
        $columnController->text('user_name')->setLabel('MR Name');
        $columnController->text('chemist_code')->setLabel('Chemist Code');
        $columnController->text('chemist_name')->setLabel('Chemist Name');
        $columnController->text("date")->setLabel("Allocated Date");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('team')->setLabel("Team")->setLink('team')->setValidations('');
        $inputController->ajax_dropdown('user')->setWhere(['tm_id'=>'{team}','u_tp_id'=>config('shl.field_manager_type').'|'.config('shl.medical_rep_type').'|'.config('shl.product_specialist_type')])->setLabel("user")->setLink('user')->setValidations('');
        $inputController->setStructure(['team','user']);
    }
}