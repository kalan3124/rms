<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Auth;
use App\Models\UserAttendance;

class AppUsageController extends ReportController
{
    protected $title = "App Usage Report";

    protected $defaultSortColumn="tm_code";

    public function search(Request $request){

        $queryMr = DB::table('teams AS t')
        ->join('team_users AS tu','t.tm_id','=','tu.tm_id')
        ->join('users AS u','u.id','=','tu.u_id')
        ->join('user_types AS ut','u.u_tp_id','=','ut.u_tp_id')
        ->where('t.deleted_at',NULL)
        ->where('tu.deleted_at',NULL)
        ->where('u.deleted_at',NULL)
        ->select([
            't.tm_code',
            't.tm_name',
            'u.u_code',
            'u.name',
            'ut.user_type',
            'u.id'
        ])
        ->groupBy('tu.u_id');

        $queryFm = DB::table('teams AS t')
        ->join('users AS u','u.id','=','t.fm_id')
        ->join('user_types AS ut','u.u_tp_id','=','ut.u_tp_id')
        ->where('t.deleted_at',NULL)
        ->where('u.deleted_at',NULL)
        ->select([
            't.tm_code',
            't.tm_name',
            'u.u_code',
            'u.name',
            'ut.user_type',
            'u.id'
        ])
        ->groupBy('u.id');

        if($request->has('values.tm_id.value')){
            $queryMr->where('t.tm_id',$request->input('values.tm_id.value'));
            $queryFm->where('t.tm_id',$request->input('values.tm_id.value'));
        }

        if($request->has('values.user.value')){
            $queryMr->where('u.id',$request->input('values.user.value'));
            $queryFm->where('u.id',$request->input('values.user.value'));
        }

        if($request->has('values.division.value')){
            $queryMr->where('u.divi_id',$request->input('values.division.value'));
            $queryFm->where('u.divi_id',$request->input('values.division.value'));
        }

        $resultsMr = collect($queryMr->get());
        $resultsFm = collect($queryFm->get());

        $resultsMr->transform(function($tm){
            $app_version = UserAttendance::where('u_id',$tm->id)->latest()->first();
            $app_version = $app_version['app_version'];
            return [
                'tm_code' => $tm->tm_code,
                'tm_name' => $tm->tm_name,
                'u_code' => $tm->u_code,
                'name' => $tm->name,
                'app_version' => $app_version?$app_version:"-",
                'user_type' => $tm->user_type
            ];
        });

        $resultsFm->transform(function($fm){
            $app_version = UserAttendance::where('u_id',$fm->id)->latest()->first();
            $app_version = $app_version['app_version'];
            return [
                'tm_code' => $fm->tm_code,
                'tm_name' => $fm->tm_name,
                'u_code' => $fm->u_code,
                'name' => $fm->name,
                'app_version' => $app_version?$app_version:"-",
                'user_type' => $fm->user_type
            ];
        });

        $allResult = $resultsMr->merge($resultsFm);
        $allResult->all();

        return [
            'count'=>0,
            'results'=>$allResult
        ];
    }

    public function setColumns($columnController, Request $request){
        $columnController->text('tm_code')->setLabel("Team Code");
        $columnController->text('tm_name')->setLabel("Team Name");
        $columnController->text('u_code')->setLabel("User Code");
        $columnController->text('name')->setLabel("User Name");
        $columnController->text('user_type')->setLabel("User Type");
        $columnController->text('app_version')->setLabel("App Version");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('tm_id')->setLabel('Team')->setLink('team')->setValidations('');
        $inputController->ajax_dropdown('user')->setWhere(['u_tp_id'=>'2|3'.'|'.config('shl.product_specialist_type'),'tm_id'=>'{tm_id}','divi_id'=>'{division}'])->setLabel('PS/MR')->setLink('user')->setValidations('');
        $inputController->ajax_dropdown('division')->setLabel('Division')->setLink('division')->setValidations('');
        
        $inputController->setStructure([
            ['tm_id','user'],
            'division'
        ]);
    }
}