<?php

namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use App\Models\ProductiveVisit;
use App\Models\TeamUser;
use App\Models\User;
use App\Models\UserTeam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductiveController extends ReportController
{
    protected $title = "Productive Report";

    protected $defaultSortColumn="pro_start_time";

    public function search(Request $request){

        $values = $request->input('values');

        $dropdowns = ['doc_id','chemist_id','u_id','hos_stf_id'];

        $query = ProductiveVisit::from('productive_visit')
        ->select(['productive_visit.*','u.divi_id'])
        ->join('users AS u','productive_visit.u_id','u.id')
        ->where('u_id','!=',2)
        ->where('u_id','!=',5);

        if(isset($values['divi_id'])){
            $query->where('u.divi_id',$values['divi_id']['value']);
        }

        foreach($dropdowns as $name){
            if(isset($values[$name])){
                $query->where($name,$values[$name]['value']);
            }
        }

        if(!isset($values['u_id'])){
            
            $user = Auth::user();
            /** @var \App\Models\User $user */
    
            if(in_array($user->getRoll(),[
                config('shl.product_specialist_type'),
                config('shl.medical_rep_type'),
                config('shl.field_manager_type')
            ])){
                $users = User::getByUser($user);
                $query->whereIn('productive_visit.u_id',$users->pluck('u_id')->all());
            }

            $teams = UserTeam::where('u_id',$user->getKey())->get();
            if($teams->count()){
                $users = TeamUser::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
                $query->whereIn('productive_visit.u_id',$users->pluck('u_id')->all());
            }  
        }

        if(isset($values['s_date'])&&isset($values['e_date'])){
            $query->whereDate('productive_visit.pro_start_time',">=",date("Y-m-d",strtotime($values['s_date'])));
            $query->whereDate('productive_visit.pro_start_time',"<=",date("Y-m-d",strtotime($values['e_date'])));
        }

        $query->with(["doctor","otherHospitalStaff","chemist","user","visitType","details","details.product","details.sampling","details.detailing","details.promotion"]);

        $count = $this->paginateAndCount($query,$request);

        $results = $query->get();

        // return $results;

        $results->transform(function($row){
            $doctor = null;
            $chemist = null;
            $otherHospitalStaff = null;

            if($row->doctor){
                $doctor = [
                    "label"=>$row->doctor->doc_name,
                    "value"=>$row->doctor->getKey()
                ];
            }

            if($row->chemist){
                $chemist = [
                    "label"=>$row->chemist->chemist_name,
                    "value"=>$row->chemist->getKey()
                ];
            }

            if($row->otherHospitalStaff){
                $otherHospitalStaff = [
                    "label"=>$row->otherHospitalStaff->hos_stf_name,
                    'value'=>$row->otherHospitalStaff->getKey()
                ];
            }

            $row->details->transform(function($detail){
                return [
                    "product"=>$detail->product->product_name??"DELETED",
                    "smpl_rsn"=>$detail->sampling->rsn_name??"DELETED",
                    "dtl_rsn"=>$detail->detailing->rsn_name??"DELETED",
                    "prm_rsn"=>$detail->promotion->rsn_name??"DELETED",
                    "qty"=>$detail->qty,
                    "remark"=>$detail->remark
                ];
            });

            return [
                "pro_visit_no"=>$row->pro_visit_no,
                'app_version'=>$row->app_version,
                "doc_chem_id"=>(isset($doctor)?$doctor:(isset($chemist)?$chemist:$otherHospitalStaff)),
                "u_id"=>[
                    "value"=> $row->user? $row->user->getKey():0,
                    "label"=>$row->user?$row->user->name:"DELETED"
                ],
                "is_shedule"=>[
                    "label"=>$row->is_shedule?"Un Sheduled":"Sheduled",
                    "value"=>$row->is_shedule
                ],
                "visited_place"=>[
                    "value"=>$row->visitType->getKey()??0,
                    "label"=>$row->visitType->vt_name??"DELETED"
                ],
                "pro_start_time"=>$row->pro_start_time,
                "pro_end_time"=>$row->pro_end_time,
                "pro_summary"=>$row->pro_summary,
                "audio"=>$row->audio_path,
                "details"=>[
                    "products"=>$row->details,
                    "title"=>$row->pro_visit_no
                ]
            ];
        });

        return [
            'count'=>$count,
            'results'=>$results
        ];

    }

    protected function setColumns($columnController, Request $request){
        $columnController->text('pro_visit_no')->setLabel("Productive Code");
        $columnController->ajax_dropdown('doc_chem_id')->setLabel("Doctor/Chemist/OHS");
        $columnController->ajax_dropdown('u_id')->setLabel("MR or FM");
        $columnController->select("is_shedule")->setLabel("Shedule Status");
        $columnController->ajax_dropdown("visited_place")->setLabel("Visited Place");
        $columnController->text("pro_start_time")->setLabel("Start Time");
        $columnController->text("pro_end_time")->setLabel("End Time");
        $columnController->text("pro_summary")->setLabel("Summary");
        $columnController->text("app_version")->setLabel("App Version");
        $columnController->custom("audio")->setLabel("Audio Clip")->setComponent('Audio');
        $columnController->custom("details")->setLabel("Products")->setComponent('ProductiveDetails');
    }

    protected function setInputs($inputController){
        $inputController->ajax_dropdown("doc_id")->setLabel("Doctor")->setLink("doctor")->setValidations('');
        $inputController->ajax_dropdown("chemist_id")->setLabel("Chemist")->setLink("chemist")->setValidations('');
        $inputController->ajax_dropdown("hos_stf_id")->setLabel("Other staff")->setLink("other_hospital_staff")->setValidations('');
        // $inputController->ajax_dropdown("u_id")->setLabel("Mr or FM")->setLink("user");
        $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team')->setValidations('');
        $inputController->ajax_dropdown("u_id")->setWhere(['tm_id'=>"{team}",'divi_id'=>"{divi_id}"])->setLabel("MR/PS or FM")->setLink("user")->setValidations('');
        $inputController->ajax_dropdown('divi_id')->setLabel('Division')->setLink('division')->setValidations('');
        $inputController->date("s_date")->setLabel("From");
        $inputController->date("e_date")->setLabel("To");
        $inputController->setStructure([["divi_id","doc_id","chemist_id","hos_stf_id"],["team","u_id","s_date","e_date"]]);
    }
}