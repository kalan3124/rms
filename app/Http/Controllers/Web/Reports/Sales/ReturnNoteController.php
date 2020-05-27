<?php 
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Area;
use Illuminate\Http\Request;
use App\Models\SfaReturnNote;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReturnNoteController extends ReportController{

    protected $title = "Return Notes Report";

    public function search($request){

        $user = Auth::user();

        if($user){
            $userCode = substr($user->u_code,0,4);

            $area = Area::where('ar_code',$userCode)->first();
        }

        $values = $request->input('values');

        $query = SfaReturnNote::with(['chemist','user']);

        if(isset($values['user'])){
            $query->where('u_id',$values['user']['value']);
        }

        if(isset($values['chemist'])){
            $query->where('chemist_id',$values['chemist']['value']);
        }

        if(isset($values['s_date']) && isset($values['e_date'])){
            $query->whereDate('rn_time','>=',$values['s_date']);
            $query->whereDate('rn_time','<=',$values['e_date']);
        }

        if($user->getRoll() == config('shl.area_sales_manager_type')){
            if(isset($area->ar_code)){
                $users = User::where('u_code','LIKE','%'.$area->ar_code.'%')->get();
                $query->whereIn('u_id',$users->pluck('id')->all());
            }
        }

        $count = $this->paginateAndCount($query,$request,'rn_no');

        $results = $query->get();

        $results->transform(function($rn){
            return [
                'rn_no'=>$rn->rn_no,
                'u_id'=>$rn->user?[
                    'value'=>$rn->user->getKey(),
                    'label'=>$rn->user->name
                ]:[
                    'value'=>0,
                    'label'=>"DELETED"
                ],
                'chemist_id'=>$rn->chemist?[
                    'value'=>$rn->chemist->getKey(),
                    'label'=>$rn->chemist->chemist_name
                ]:[
                    'value'=>0,
                    'label'=>"DELETED"
                ],
                'remark'=>$rn->remark,
                'rn_time'=>$rn->rn_time,
                'sr_availability'=>$rn->sr_availability==1?"Yes":"No",
                'mr_availability'=>$rn->mr_availability==1?"Yes":"No",
                'latitude'=>$rn->latitude,
                'longitude'=>$rn->longitude,
                'battery_lvl'=>$rn->battery_level,
                'app_version'=>$rn->app_version
            ];
        });

        return [
            'results'=>$results,
            'count'=>$count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('rn_no')->setLabel('ReturnNote No.');
        $columnController->ajax_dropdown('u_id')->setLabel('User');
        $columnController->ajax_dropdown('chemist_id')->setLabel('Chemist');
        
        $columnController->text('remark')->setLabel('Remark');
        $columnController->date('rn_time')->setLabel('Date');
        $columnController->text('sr_availability')->setLabel('Sales Return');
        $columnController->text('mr_availability')->setLabel('Market Return');
        $columnController->text('latitude')->setLabel('Latitude');
        $columnController->text('longitude')->setLabel('Longitude');
        $columnController->text('battery_lvl')->setLabel('Battery Level');
        $columnController->text("app_version")->setLabel("App Version");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id'=>config('shl.sales_rep_type')])->setValidations('');
        $inputController->ajax_dropdown('chemist')->setLabel('Chemist')->setLink('chemist')->setValidations('');
        $inputController->date('s_date')->setLabel("From")->setValidations('');
        $inputController->date('e_date')->setLabel("To")->setValidations('');

        $inputController->setStructure([['user','chemist'],['s_date','e_date']]);
    }
}
