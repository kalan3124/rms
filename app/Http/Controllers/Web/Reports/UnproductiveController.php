<?php

namespace App\Http\Controllers\Web\Reports;

use App\Models\TeamUser;
use Illuminate\Http\Request;
use App\Models\UnproductiveVisit;
use Illuminate\Support\Facades\Auth;
use App\Models\User as UserModel;
use App\Models\UserTeam;

class UnproductiveController extends ReportController
{
    protected $title = "Unproductive report";

    protected $defaultSortColumn="unpro_time";

    public function search(Request $request){

        $values = $request->input('values');

        $query = UnproductiveVisit::query();

        $selectBoxes = [
            'doc_id',
            'chemist_id',
            'is_shedule',
            'reason_id'
        ];

        foreach($selectBoxes as $name){
            if(isset($values[$name])){
                $query->where($name,$values[$name]['value']);
            }
        }

        if(isset($values['s_date'])&&isset($values['e_date'])){
            $query->whereDate('unpro_time',">=",date("Y-m-d",strtotime($values['s_date'])));
            $query->whereDate('unpro_time',"<=",date("Y-m-d",strtotime($values['e_date'])));
        }

        $user = Auth::user();
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $users = UserModel::getByUser($user);
            $query->whereIn('u_id',$users->pluck('u_id')->all());
        }


        $teams = UserTeam::where('u_id',$user->getKey())->get();
        if($teams->count()){
            $users = TeamUser::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
            $query->whereIn('u_id',$users->pluck('u_id')->all());
        }  

        $count = $this->paginateAndCount($query,$request);

        $query->with([
            'doctor',
            'chemist',
            'reason'
        ]);

        $results = $query->get();

        $results->transform(function($result){
            $doctor = null;
            $chemist = null;

            if($result->doctor){
                $doctor = [
                    "label"=>$result->doctor->doc_name,
                    "value"=>$result->doctor->getKey()
                ];
            }

            if($result->chemist){
                $chemist = [
                    "label"=>$result->chemist->chemist_name,
                    "value"=>$result->chemist->getKey()
                ];
            }

            return [
                "doc_id"=>$doctor,
                'app_version'=>$result->app_version,
                "chemist_id"=>$chemist,
                "is_shedule"=>[
                    "label"=>$result->is_shedule?"Sheduled":"Un Sheduled",
                    "value"=>$result->is_shedule
                ],
                "reason_id"=>[
                    "label"=>$result->reason->rsn_name,
                    "value"=>$result->reason->getKey()
                ],
                "lat"=>$result->lat,
                "lon"=>$result->lon,
                "unpro_time"=>$result->unpro_time,
                "un_visit_no"=>$result->un_visit_no,
            ];
        });


        return [
            'count'=>$count,
            'results'=>$results
        ];

    }

    public function setColumns($columnController, Request $request){
        $columnController->text('un_visit_no')->setLabel("Unproductive Code");
        $columnController->ajax_dropdown('doc_id')->setLabel("Doctor");
        $columnController->ajax_dropdown('chemist_id')->setLabel("Chemist");
        $columnController->select("is_shedule")->setLabel("Shedule Status");
        $columnController->ajax_dropdown("reason_id")->setLabel("Reason");
        $columnController->text("lat")->setLabel("Latitude");
        $columnController->text("lon")->setLabel("Longitude");
        $columnController->text("unpro_time")->setLabel("Time")->setSearchable(true);
        $columnController->text("app_version")->setLabel("App Version")->setSearchable(true);
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown("doc_id")->setLabel("Doctor")->setLink("doctor")->setValidations('');
        $inputController->ajax_dropdown("chemist_id")->setLabel("Chemist")->setLink("chemist")->setValidations('');
        $inputController->select("is_shedule")->setLabel("Shedule Status")->setOptions([
            0=>"Un Sheduled",
            1=>"Sheduled",
        ])->setValidations('');
        $inputController->ajax_dropdown("reason_id")->setLabel("Reason")->setLink("reason")->setWhere([
            "rsn_type"=>config("shl.unproductive_reason_type")
        ])->setValidations('');
        $inputController->date("s_date")->setLabel("From");
        $inputController->date("e_date")->setLabel("To");

        $inputController->setStructure([["doc_id","chemist_id","reason_id"],["s_date","e_date","is_shedule"]]);

    }
}