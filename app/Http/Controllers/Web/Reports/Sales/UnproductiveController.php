<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Area;
use Illuminate\Http\Request;
use App\Models\SfaUnproductiveVisit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class UnproductiveController extends ReportController {

    protected $title = "Unproductive Report";

    public function search($request){

        $user = Auth::user();

        if($user){
            $userCode = substr($user->u_code,0,4);

            $area = Area::where('ar_code',$userCode)->first();
        }

        $values = $request->input('values');

        $query = SfaUnproductiveVisit::with(['chemist','user','reason']);

        if(isset($values['user'])){
            $query->where('u_id',$values['user']['value']);
        }

        if(isset($values['chemist'])){
            $query->where('chemist_id',$values['chemist']['value']);
        }

        if(isset($values['reason'])){
            $query->where('rsn_id',$values['reason']['value']);
        }

        if(isset($values['s_date']) && isset($values['e_date'])){
            // $query->whereDate('unpro_time','>=',$values['s_date']);
            // $query->whereDate('unpro_time','<=',$values['e_date']);

            $query->whereBetween( DB::raw( 'DATE(unpro_time)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        if($user->getRoll() == config('shl.area_sales_manager_type')){
            if(isset($area->ar_code)){
                $users = User::where('u_code','LIKE','%'.$area->ar_code.'%')->get();
                $query->whereIn('u_id',$users->pluck('id')->all());
            }
        }

        $count = $this->paginateAndCount($query,$request,'un_visit_no');

        $results = $query->get();

        $results->transform(function($un){
            return [
                'unpro_no'=>$un->un_visit_no,
                'u_id'=>$un->user?[
                    'value'=>$un->user->getKey(),
                    'label'=>$un->user->name
                ]:[
                    'value'=>0,
                    'label'=>"DELETED"
                ],
                'chemist_id'=>$un->chemist?[
                    'value'=>$un->chemist->getKey(),
                    'label'=>$un->chemist->chemist_name
                ]:[
                    'value'=>0,
                    'label'=>"DELETED"
                ],
                'unpro_time'=>$un->unpro_time,
                'latitude'=>$un->latitude,
                'longitude'=>$un->longitude,
                'battery_lvl'=>$un->battery_level,
                'app_version'=>$un->app_version,
                'rsn_id'=>$un->reason?[
                    'value'=>$un->reason->getKey(),
                    'label'=>$un->reason->rsn_name
                ]:[
                    'value'=>0,
                    'label'=>"DELETED"
                ]
            ];
        });

        return [
            'results'=>$results,
            'count'=>$count
        ];

    }

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('unpro_no')->setLabel('Unproductive No.');
        $columnController->ajax_dropdown('u_id')->setLabel('User');
        $columnController->ajax_dropdown('chemist_id')->setLabel('Chemist');

        $columnController->date('unpro_time')->setLabel('Date');
        $columnController->text('latitude')->setLabel('Latitude');
        $columnController->text('longitude')->setLabel('Longitude');
        $columnController->ajax_dropdown('rsn_id')->setLabel('Reason');
        $columnController->text('battery_lvl')->setLabel('Battery Level');
        $columnController->text("app_version")->setLabel("App Version");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id'=>config('shl.sales_rep_type')])->setValidations('');
        $inputController->ajax_dropdown('chemist')->setLabel('Chemist')->setLink('chemist')->setValidations('');
        $inputController->ajax_dropdown('reason')->setLabel('Reason')->setWhere(['rsn_type'=>7])->setLink('reason')->setValidations('');
        $inputController->date('s_date')->setLabel("From")->setValidations('');
        $inputController->date('e_date')->setLabel("To")->setValidations('');

        $inputController->setStructure(['user',['reason','chemist'],['s_date','e_date']]);
    }
}
