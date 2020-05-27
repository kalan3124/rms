<?php
namespace App\Http\Controllers\Web\Reports\Allocations;

use App\Http\Controllers\Web\Reports\ReportController;
use App\Form\Columns\ColumnController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use  App\Models\User;

class MRDoctorReportController extends ReportController{

    protected $title = "MR Doctor List";

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
            case 'doc_code':
                $sortBy = 'd.doc_code';
                break;
            case 'doc_name':
                $sortBy = 'd.doc_name';
                break;
            default:
                $sortBy = 'uc.updated_at';
                break;
        }
        
        $query = DB::table('user_customer AS uc')
            ->select(['t.tm_name','t.tm_code','u.u_code','u.name','d.doc_code','d.doc_name','uc.updated_at'])
            ->join('doctors AS d','uc.doc_id','=','d.doc_id','inner')
            ->join('users AS u','u.id','=','uc.u_id','inner')
            ->leftJoin('team_users AS tu','tu.u_id','=','uc.u_id')
            ->leftJoin('teams AS t',DB::raw('IF(tu.tm_id IS NULL,t.fm_id,t.tm_id)'),'=',DB::raw('IFNULL(tu.tm_id,uc.u_id)'))
            ->whereNull('d.deleted_at')
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
                'team'=>$row->tm_name,
                'team_code'=>$row->tm_code,
                'user_code'=>$row->u_code,
                'user_name'=> $row->name,
                'doc_code'=>$row->doc_code,
                'doc_name'=>$row->doc_name,
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
        $columnController->text('doc_code')->setLabel('Doctor Code');
        $columnController->text('doc_name')->setLabel('Doctor Name');
        $columnController->text("date")->setLabel("Allocated Date");
    }

    public function setInputs($inputController){
       $inputController->ajax_dropdown('team')->setLabel("Team")->setLink('team')->setValidations('');
       $inputController->ajax_dropdown('user')->setLabel("user")->setLink('user')->setWhere(['tm_id'=>'{team}','u_tp_id'=>config('shl.field_manager_type').'|'.config('shl.medical_rep_type').'|'.config('shl.product_specialist_type')])->setValidations('');
        $inputController->setStructure(['team','user']);
    }
}