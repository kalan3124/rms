<?php
namespace App\Http\Controllers\Web\Reports\Allocations;

use App\Http\Controllers\Web\Reports\ReportController;
use App\Form\Columns\ColumnController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class MRSubTownReportController extends ReportController{

    protected $title = "MR Territory List";

    protected $defaultSortColumn = 'tm.tm_id' ;

    public function search($request){

        switch ($request->input('sortBy')) {
            case 'team':
               $sortBy = 'tm.tm_id';
                break;
            case 'user_code':
                $sortBy = 'u.u_code';
                break;
            case 'user_name':
                $sortBy = 'u.name';
                break;
            case 'region_code':
                $sortBy = 'r.rg_code';
                break;
            case 'region_name':
                $sortBy = 'r.rg_name';
                break;
            case 'area_code':
                $sortBy = 'a.ar_code';
                break;
            case 'area_name':
                $sortBy = 'a.ar_name';
                break;
            case 'town_code':
                $sortBy = 't.twn_code';
                break;
            case 'town_name':
                $sortBy = 't.twn_name';
                break;
            case 'sub_town_code':
                $sortBy = 't.sub_twn_code';
                break;
            case 'sub_town_name':
                $sortBy = 't.sub_twn_name';
                break;
            default:
                $sortBy = 'ua.updated_at';
                break;
        }
        
        $query = DB::table('sub_town AS st')->where([
                'ua.deleted_at' => null,
                'p.deleted_at' => null,
                'd.deleted_at' => null,
                'a.deleted_at' => null,
                't.deleted_at' => null,
                'r.deleted_at' => null,
                'st.deleted_at' => null,
                'tu.deleted_at' => null,
                'tm.deleted_at' => null,
                'u.deleted_at' => null,
            ])
            ->select(['t.twn_id','tm.tm_code','t.twn_name','t.twn_code','st.sub_twn_id','st.sub_twn_name','st.sub_twn_code','a.ar_id','a.ar_name','a.ar_code','r.rg_id','r.rg_name','r.rg_code','d.dis_id','d.dis_name','d.dis_code','p.pv_id','p.pv_name','p.pv_code','tm.tm_name','u.u_code','u.name','ua.updated_at'])
            ->join('town AS t', 't.twn_id', '=', 'st.twn_id', 'left')
            ->join('area AS a', 'a.ar_id', '=', 't.ar_id', 'left')
            ->join('region AS r', 'r.rg_id', '=', 'a.rg_id', 'left')
            ->join('district AS d', 'd.dis_id', '=', 'r.dis_id', 'left')
            ->join('province AS p', 'p.pv_id', '=', 'd.pv_id', 'left')
            ->join('user_areas AS ua', function ($join) {
                $join->orOn('ua.sub_twn_id', '=', 'st.sub_twn_id');
                $join->orOn('ua.twn_id', '=', 't.twn_id');
                $join->orOn('ua.ar_id', '=', 'a.ar_id');
                $join->orOn('ua.rg_id', '=', 'r.rg_id');
                $join->orOn('ua.dis_id', '=', 'd.dis_id');
                $join->orOn('ua.pv_id', '=', 'p.pv_id');
            })
            ->join('team_users AS tu','tu.u_id','=','ua.u_id')
            ->join('teams AS tm',DB::raw('IF(tu.tm_id IS NULL,tm.fm_id,tm.tm_id)'),'=',DB::raw('IFNULL(tu.tm_id,ua.u_id)'))
            ->join('users AS u','u.id','=','ua.u_id','left')
            ->groupBy('st.sub_twn_id');

        

        if($request->has('values.team.value')){
            $query->where('tm.tm_id',$request->input('values.team.value'));
        }

       
        if($request->has('values.user.value')){
            $query->where('u.id',$request->input('values.user.value'));
        }

        if($request->has('values.region.value')){
            $query->where('r.rg_id',$request->input('values.region.value'));
        }

        if($request->has('values.area.value')){
            $query->where('a.ar_id',$request->input('values.area.value'));
        }

        if($request->has('values.town.value')){
            $query->where('t.twn_id',$request->input('values.town.value'));
        }

        if($request->has('values.sub_town.value')){
            $query->where('st.sub_twn_id',$request->input('values.sub_town.value'));
        }

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $result = $query->get();

        $result->transform(function($row){
            return [
                'team'=>$row->tm_name,
                'team_code'=>$row->tm_code,
                'user_code'=>$row->u_code,
                'user_name'=> $row->name,
                'region_code'=>$row->rg_code,
                'region_name'=>$row->rg_name,
                'area_code'=>$row->ar_code,
                'area_name'=>$row->ar_name,
                'town_code'=>$row->twn_code,
                'town_name'=>$row->twn_name,
                'sub_town_code'=>$row->sub_twn_code,
                'sub_town_name'=>$row->sub_twn_name,
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
        $columnController->text('region_code')->setLabel('Region Code');
        $columnController->text('region_name')->setLabel('Region Name');
        $columnController->text('area_code')->setLabel('Area Code');
        $columnController->text('area_name')->setLabel('Area Name');
        $columnController->text('town_code')->setLabel('Town Code');
        $columnController->text('town_name')->setLabel('Town Name');
        $columnController->text('sub_town_code')->setLabel('Sub Town Code');
        $columnController->text('sub_town_name')->setLabel('Sub Town Name');
        $columnController->text("date")->setLabel("Allocated Date");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('team')->setLabel("Team")->setLink('team')->setValidations('');
        $inputController->ajax_dropdown('user')->setLabel("User")->setLink('user')->setWhere(['tm_id'=>'{team}','u_tp_id'=>config('shl.field_manager_type').'|'.config('shl.medical_rep_type').'|'.config('shl.product_specialist_type')])->setValidations('');
        $inputController->ajax_dropdown('region')->setLabel('Region')->setLink('region')->setValidations('');
        $inputController->ajax_dropdown('area')->setLabel('Area')->setLink('area')->setWhere(['rg_id'=>'{region}'])->setValidations('');
        $inputController->ajax_dropdown('town')->setLabel('Town')->setLink('town')->setWhere(['ar_id'=>'{area}'])->setValidations('');
        $inputController->ajax_dropdown('sub_town')->setLabel('Sub Town')->setLink('sub_town')->setWhere(['twn_id'=>'{town}'])->setValidations('');
        $inputController->setStructure([['team','user'],["region","area"],["town","sub_town"]]);
    }
}