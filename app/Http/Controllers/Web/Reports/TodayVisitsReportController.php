<?php

namespace App\Http\Controllers\Web\Reports;

use App\Exceptions\WebAPIException;
use App\Models\Chemist;
use App\Models\DoctorSubTown;
use App\Models\OtherHospitalStaff;
use App\Models\User;
use App\Models\UserCustomer;
use App\Traits\Territory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TodayVisitsReportController extends ReportController{

    use Territory;

    protected $title = "Visits Report";

    protected $defaultSortColumn="name";

    public function search(Request $request){

        $validation = Validator::make($request->all(),[
            'values'=>'required|array',
            'values.date'=>'required|string',
            'values.u_id'=>'required|array',
            'values.u_id.value'=>'required|exists:users,id'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Date field and user field is required.");
        }

        $date = $request->input('values.date');
        $userId = $request->input('values.u_id.value');

        $user = User::find($userId);

        $assignedCustomers = UserCustomer::getByUser($user);

        $doctorIds = $assignedCustomers->pluck('doc_id');
        $chemistIds = $assignedCustomers->pluck('chemist_id');

        $itinerarySubTownIds = [];
        
        try {
            $itineraryTowns = $this->getTerritoriesByItinerary($user);
        } catch (\Exception $e){
            throw new WebAPIException("Can not find an itinerary for this user.");
        }

        $itinerarySubTownIds = $itineraryTowns->pluck('sub_twn_id');

        // Getting chemists for above time ids
        $chemists = Chemist::with('sub_town')->whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('chemist_id',$chemistIds)->get();

        // Transforming results
        $chemists->transform(function($chem,$key){
            return [
                'id'=>'C'.$chem->getKey(),
                "town_name"=>$chem->sub_town?$chem->sub_town->sub_twn_name:"NOT SET",
                "town_code"=>$chem->sub_town?$chem->sub_town->sub_twn_code:"NOT SET",
                "customer_type"=>'CHEMIST',
                "customer_name"=>$chem->chemist_name,
                'customer_code'=>$chem->chemist_code
            ];
        });

        // Getting doctors for today
        $doctors = DB::table('doctor_intitution AS ti')
            ->join('institutions AS i','i.ins_id','=','ti.ins_id','inner')
            ->join('sub_town AS st','st.sub_twn_id','i.sub_twn_id')
            ->join('doctors AS d','d.doc_id','=','ti.doc_id')
            ->whereIn('i.sub_twn_id',$itinerarySubTownIds)
            ->where([
                'i.deleted_at'=>null,
                'ti.deleted_at'=>null
            ])
            ->whereIn('ti.doc_id',$doctorIds)
            ->select(DB::raw('d.*'),DB::raw('st.*'))
            ->groupBy('ti.doc_id')
            ->get();
        
        // Transforming results
        $doctors->transform(function($doctor,$key){
            return [
                'id'=>'D'.$doctor->doc_id,
                "town_name"=>$doctor->sub_twn_name,
                "town_code"=>$doctor->sub_twn_code,
                "customer_type"=>'DOCTOR',
                "customer_name"=>$doctor->doc_name,
                'customer_code'=>$doctor->doc_code
            ];
        });


        $doctorsBySubTown = DoctorSubTown::with(['doctor','subTown'])->whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('doc_id',$doctorIds)->get();

        $doctorsBySubTown->transform(function($doctorSubTown,$key){
            return [
                "id"=>'D'.($doctorSubTown->doctor?$doctorSubTown->doctor->doc_name:0),
                "town_name"=>$doctorSubTown->subTown->sub_twn_name,
                "town_code"=>$doctorSubTown->subTown->sub_twn_code,
                "customer_type"=>'DOCTOR',
                "customer_name"=>$doctorSubTown->doctor->doc_name,
                'customer_code'=>$doctorSubTown->doctor->doc_code
            ];
        });

        // Merge doctors and chemists
        $visits = $doctors->merge($doctorsBySubTown);
        $visits->groupBy('id');
        $visits = $visits->merge($chemists);

        // Getting Other Hospital Staff
        $otherStaff = OtherHospitalStaff::with('sub_town')->whereIn('sub_twn_id',$itinerarySubTownIds)->get();

        $otherStaff->transform(function($otherStaff,$key){
            return [
                "id"=>'S'.$otherStaff->hos_stf_id,
                "town_name"=>$otherStaff->sub_town?$otherStaff->sub_town->sub_twn_name:"DELETED",
                "town_code"=>$otherStaff->sub_town?$otherStaff->sub_town->sub_twn_code:"DELETED",
                "customer_type"=>'HOSPITAL STAFF',
                "customer_name"=>$otherStaff->hos_stf_name,
                'customer_code'=>$otherStaff->hos_stf_code
            ];
        });
        // merge other hospital staff with chemist and doctors
        $visits = $visits->merge($otherStaff);

        $visits->sortBy('id');
        
        return [
            'count'=>0,
            'results'=>$visits
        ];
    }

    public function setColumns($columnController, Request $request){
        $columnController->text('town_code')->setLabel("Town Code");
        $columnController->text('town_name')->setLabel("Town Name");
        $columnController->text('customer_type')->setLabel("Customer Type");
        $columnController->text('customer_code')->setLabel("Customer Code");
        $columnController->text('customer_name')->setLabel("Customer Name");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('divi_id')->setLabel('Division')->setLink('division')->setValidations('');
        $inputController->ajax_dropdown('tm_id')->setLabel('Team')->setLink('team')->setValidations('');
        $inputController->ajax_dropdown('u_id')->setWhere(['tm_id'=>"{tm_id}",'divi_id'=>"{divi_id}"])->setLabel('User')->setLink('user');
        $inputController->date("date")->setLabel('Date');

        $inputController->setStructure([
            ['divi_id','tm_id'],
            ['u_id','date'],
        ]);
    }
}