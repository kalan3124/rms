<?php

namespace App\Http\Controllers\API\Medical\V1;

use App\Http\Controllers\Controller;
use App\Exceptions\MediAPIException;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Itinerary;
use App\Models\Chemist;
use App\Models\User;
use App\Models\SubTown;
use App\Models\UserCustomer;

use App\Traits\Territory;
use App\Models\DoctorSubTown;

use App\Models\OtherHospitalStaff;
use App\Models\ItineraryDate;

use App\Models\ProductiveVisit;
use App\Models\UnproductiveVisit;
use App\Models\Doctor;
use App\Models\ItineraryDateChange;
use App\Models\StandardItineraryDateCustomer;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Validator;

class VisitsController extends Controller {
    use Territory;

    protected function checkItineraryHasChanged($user,$timestamp){
        // Checking the weather itinerary details are modified or not
        if($timestamp){
            $itinerary = Itinerary::where('i_year',date('Y'))
                ->where('i_month',date('m'))
                ->where(function($query) use($user) {
                    $query->orWhere('rep_id',$user->getKey());
                    $query->orWhere('fm_id',$user->getKey());
                })
                ->whereNotNull('i_aprvd_at')
                ->latest()
                ->first();

            $changedItinerary = ItineraryDateChange::where('u_id',$user->getKey())
                ->whereNotNull('idc_aprvd_u_id')
                ->whereDate('idc_date',date('Y-m-d'))
                ->latest()
                ->first();

            if(($itinerary&&strtotime($itinerary->updated_at)<$timestamp/1000)||($changedItinerary&&strtotime($changedItinerary->updated_at)<$timestamp/1000)){
                // throw new MediAPIException("You have no latest data.",38);
            }
        }

    }
    /**
     * Returning all chemists and doctors for the current date
     *
     * @return Illuminate\Http\JsonResponse
     * @throws MediAPIException
     */
    public function getAllForToday(Request $request){

        $user = Auth::user();

        $assignedCustomers = UserCustomer::getByUser($user);

        $doctorIds = $assignedCustomers->pluck('doc_id');
        $chemistIds = $assignedCustomers->pluck('chemist_id');

        $doctorsByStandardItinerary = false;
        $chemistsByStandardItinerary = false;

        $itinerarySubTownIds = [];

        $this->checkItineraryHasChanged($user,$request->input('timestamp'));

        $itineraryDate = ItineraryDate::getTodayForUser($user,['changedItineraryDate']);
        $itineraryTowns = $this->getTerritoriesByItinerary($user);

        $itinerarySubTownIds = $itineraryTowns->pluck('sub_twn_id');
        if(!$itineraryDate->changedItineraryDate&&$itineraryDate->sid_id) {
            $standardItineraryCustomers = StandardItineraryDateCustomer::where('sid_id',$itineraryDate->sid_id)->get();

            $oldChemistIds = $standardItineraryCustomers->pluck('chemist_id')->filter(function($item){return !!$item;})->all();
            if(count($oldChemistIds)){
                $chemistIds = $oldChemistIds;
                $chemistsByStandardItinerary = true;
            }
            $oldDoctorIds = $standardItineraryCustomers->pluck('doc_id')->filter(function($item){return !!$item;})->all();
            if(count($oldDoctorIds)){
                $doctorIds = $oldDoctorIds;
                $doctorsByStandardItinerary = true;
            }
            $otherHospitalStaffIds = $standardItineraryCustomers->pluck('hos_stf_id')->filter(function($item){return !!$item;})->all();
        }

        // Getting chemists for above time ids
        if($chemistsByStandardItinerary)
            $chemists = Chemist::whereIn('chemist_id',$chemistIds)->get();
        else
            $chemists = Chemist::whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('chemist_id',$chemistIds)->get();

        // Transforming results
        $chemists->transform(function($chem,$key){
            return [
                "visit_id"=>$key+1,
                "visit_date"=>date("Y-m-d"),
                // 1 = chem, 0= doc
                "visit_type"=>1,
                "is_schedule"=>1,
                // Doctor or chemistry
                "doc_chem_id"=>$chem->getKey()
            ];
        });

        // Getting doctors for today
        $doctors = DB::table('doctor_intitution AS ti')
            ->join('institutions AS i','i.ins_id','=','ti.ins_id','inner')
            ->where(function(Builder $query) use($itinerarySubTownIds, $doctorsByStandardItinerary) {
                if(!$doctorsByStandardItinerary)
                    $query->whereIn('i.sub_twn_id',$itinerarySubTownIds);
            })
            ->where([
                'i.deleted_at'=>null,
                'ti.deleted_at'=>null
            ])
            ->whereIn('doc_id',$doctorIds)
            ->select('ti.doc_id')
            ->groupBy('ti.doc_id')
            ->get();

        $chemistsCount = $chemists->count();

        // Transforming results
        $doctors->transform(function($doctor,$key)use($chemistsCount){
            return [
                "visit_id"=>$key+$chemistsCount+1,
                "visit_date"=>date("Y-m-d"),
                // 1 = chem, 0= doc
                "visit_type"=>0,
                "is_schedule"=>1,
                // Doctor or chemistry
                "doc_chem_id"=>$doctor->doc_id
            ];
        });

        $totalCount = $chemistsCount + $doctors->count();

        if(!$doctorsByStandardItinerary)
            $doctorsBySubTown = DoctorSubTown::with('doctor')->whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('doc_id',$doctorIds)->get();
        else
            $doctorsBySubTown = DoctorSubTown::with('doctor')->whereIn('doc_id',$doctorIds)->get();

        $doctorsBySubTown->transform(function($doctor,$key)use($totalCount){
            return [
                "visit_id"=>$key+$totalCount+1,
                "visit_date"=>date("Y-m-d"),
                // 1 = chem, 0= doc
                "visit_type"=>0,
                "is_schedule"=>1,
                // Doctor or chemistry
                "doc_chem_id"=>$doctor->doctor->doc_id
            ];
        });

        // Merge doctors and chemists
        $visits = $doctors->merge($doctorsBySubTown);
        $visits->groupBy('doc_chem_id');
        $visits = $visits->merge($chemists);

        if(isset($standardItineraryCustomers)&&$standardItineraryCustomers->count()){
            $otherStaff =  OtherHospitalStaff::with('sub_town')->whereIn('hos_stf_id',$otherHospitalStaffIds)->get();
        } else {
            // Getting Other Hospital Staff
            $otherStaff = OtherHospitalStaff::with('sub_town')->whereIn('sub_twn_id',$itinerarySubTownIds)->get();
        }

        $finalCount = $totalCount + $doctorsBySubTown->count();

        $otherStaff->transform(function($otherStaff,$key)use($finalCount){
            return [
                "visit_id"=>$key+$finalCount+1,
                "visit_date"=>date("Y-m-d"),
                // 1 = chem, 0= doc, 2= OtherStaff
                "visit_type"=>2,
                "is_schedule"=>1,
                // Doctor or chemistry or Other
                "doc_chem_id"=>$otherStaff->hos_stf_id
            ];
        });
        // merge other hospital staff with chemist and doctors
        $visits = $visits->merge($otherStaff);

        $visits->sortBy('visit_id');

        return [
            'result'=>!$visits->isEmpty(),
            'visits'=>$visits,
            'count'=>$visits->count()
        ];
    }

    /**
     * Returning the joint field worker
     *
     * @return Illuminate\Http\JsonResponse
     * @throws MediAPIException
     */
    public function getJointFieldWorker(Request $request){

        $user = Auth::user();

        $this->checkItineraryHasChanged($user,$request->input('timestamp'));

        $jointWorker = null;

        if($user->getRoll()==config("shl.field_manager_type")){
            $itineraryDate = ItineraryDate::getTodayForUser($user,[],null,true);

            $jointWorker= $itineraryDate->joinFieldWorker;

        } else {
            $itineraryDate = ItineraryDate::with('itinerary','itinerary.fieldManager')
                ->where('u_id',$user->getKey())
                ->where('id_date',date('d'))
                ->latest()
                ->first();

            if(!$itineraryDate||!$itineraryDate->itinerary||!$itineraryDate->itinerary->fieldManager)
                throw new MediAPIException("You haven't any joint field worker.",22);

            $latestItineraryDate = ItineraryDate::getTodayForUser($itineraryDate->itinerary->fieldManager,[],null,true);

            if(!$latestItineraryDate||$latestItineraryDate->getKey()!=$itineraryDate->getKey())
                $jointWorker=null;
            else
                $jointWorker=$itineraryDate->itinerary->fieldManager;
        }

        if(!$jointWorker) throw new MediAPIException("You haven't any joint field worker.",22);

        return response()->json([
            'result'=>true,
            'join_field_details'=>[[
                'jf_user_id'=>$jointWorker->getKey(),
                'jf_name'=>$jointWorker->getName(),
                'designation'=>$jointWorker->getRollName()??"",
                'designation_shortname'=>$jointWorker->getRollName()??""
            ]]
        ]);

    }

    /**
     * Returning the last five visit
     *
     * @return Illuminate\Http\JsonResponse
     * @throws MediAPIException
     */
    public function getLastFiveVisits(Request $request){
        $user = Auth::user();

        $this->checkItineraryHasChanged($user,$request->input('timestamp'));
        // Getting assigned customers for user
        $assignedCustomers = UserCustomer::getByUser($user);

        $doctorIds = $assignedCustomers->pluck('doc_id');
        $chemistIds = $assignedCustomers->pluck('chemist_id');

        $itineraryTowns = $this->getTerritoriesByItinerary($user);

        $itinerarySubTownIds = [];

        $itinerarySubTownIds = $itineraryTowns->pluck('sub_twn_id');

        // Getting chemists for above time ids
        $chemists = Chemist::whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('chemist_id',$chemistIds)->get();
        //assigned Chemist Ids
        $assignedChemId = $chemists->pluck('chemist_id');

        // Getting doctors for today
        $doctors = DB::table('doctor_intitution AS ti')
            ->join('institutions AS i','i.ins_id','=','ti.ins_id','inner')
            ->whereIn('i.sub_twn_id',$itinerarySubTownIds)
            ->where([
                'i.deleted_at'=>null,
                'ti.deleted_at'=>null
            ])
            ->whereIn('doc_id',$doctorIds)
            ->select('ti.doc_id')
            ->groupBy('ti.doc_id')
            ->get();
        //getting doctors from subtown assignment
        $doctorsBySubTown = DoctorSubTown::whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('doc_id',$doctorIds)->get();

        //merge subtown doctors with institute assigned doctors
        $doctors = $doctors->merge($doctorsBySubTown);
        //assigned doctor Ids
        $assignedDocId = $doctors->pluck('doc_id');

        //Getting Other hospital staff ids
        $otherStaff = OtherHospitalStaff::whereIn('sub_twn_id',$itinerarySubTownIds)->get();
        //assigned OtherHospital Ids
        $assignedhosid = $otherStaff->pluck('hos_stf_id');

        $allFiveVisits = collect();

        //get chemist wise last 5 visits
        foreach ($assignedChemId as $chm){
            $chemProductiveVisit = ProductiveVisit::with('second_user','promotion')
            ->where('chemist_id','=',$chm)
            ->orderBy('pro_visit_id','desc')->limit(5)
            ->get();

            $allFiveVisits = $allFiveVisits->concat($chemProductiveVisit);
        }

        //get doctor wise last 5 visits
        foreach ($assignedDocId as $dc){
            $docProductiveVisit = ProductiveVisit::with('second_user','promotion')
            ->where('doc_id','=',$dc)
            ->orderBy('pro_visit_id','desc')->limit(5)
            ->get();

            $allFiveVisits = $allFiveVisits->concat($docProductiveVisit);
        }

        //get hospital user wise last 5 visits
        foreach ($assignedhosid as $hos){
            $hosProductiveVisit = ProductiveVisit::with('second_user','promotion')
            ->where('hos_stf_id','=',$hos)
            ->orderBy('pro_visit_id','desc')->limit(5)
            ->get();

            $allFiveVisits = $allFiveVisits->concat($hosProductiveVisit);
        }

        $allFiveVisits->transform(function($pv){
            if($pv->visit_type == 0){
                $doc_chem_id = $pv->doc_id;
            } elseif($pv->visit_type == 1){
                $doc_chem_id = $pv->chemist_id;
            }else{
                $doc_chem_id = $pv->hos_stf_id;
            }
            $pro_date = date('Y-m-d', strtotime($pv->pro_start_time));
            return [
                'visit_id'=>$pv->pro_visit_id,
                'doc_chem_id'=>$doc_chem_id,
                'visit_type_id'=>$pv->visit_type,
                'is_schedule'=>$pv->is_shedule,
                'promotion'=>$pv->promo_id?$pv->promotion->promo_name:"",
                'promo_remark'=>$pv->promo_remark,
                'productive_summary'=>$pv->pro_summary,
                'join_field_id'=>$pv->second_user?$pv->second_user->name:"",
                'visit_date'=>$pro_date
            ];
        });
        return [
            "result"=>true,
            "visit_details"=>$allFiveVisits
        ];
    }
    /**
     * Returning last visit details for a day
     *
     * @param Request $request required day as a string
     * @return Illuminate\Http\JsonResponse
     */
    public function getLastFiveVisitDetails(Request $request){
        $user = Auth::user();

        $this->checkItineraryHasChanged($user,$request->input('timestamp'));
        // Getting assigned customers for user
        $assignedCustomers = UserCustomer::getByUser($user);

        $doctorIds = $assignedCustomers->pluck('doc_id');
        $chemistIds = $assignedCustomers->pluck('chemist_id');

        $itineraryTowns = $this->getTerritoriesByItinerary($user);

        $itinerarySubTownIds = [];
        // If itinerary towns not set return all town ids
        // Other wise take only town ids from above results
        if($itineraryTowns->isEmpty()){
            $itinerarySubTownIds = SubTown::all()->pluck('sub_twn_id');
        }
        else {
            $itinerarySubTownIds = $itineraryTowns->pluck('sub_twn_id');
        }

        // Getting chemists for above time ids
        $chemists = Chemist::whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('chemist_id',$chemistIds)->get();
        //assigned Chemist Ids
        $assignedChemId = $chemists->pluck('chemist_id');

        // Getting doctors for today
        $doctors = DB::table('doctor_intitution AS ti')
            ->join('institutions AS i','i.ins_id','=','ti.ins_id','inner')
            ->whereIn('i.sub_twn_id',$itinerarySubTownIds)
            ->where([
                'i.deleted_at'=>null,
                'ti.deleted_at'=>null
            ])
            ->whereIn('doc_id',$doctorIds)
            ->select('ti.doc_id')
            ->groupBy('ti.doc_id')
            ->get();
            //getting doctors from subtown assignment
        $doctorsBySubTown = DoctorSubTown::whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('doc_id',$doctorIds)->get();

        //merge subtown doctors with institute assigned doctors
        $doctors = $doctors->merge($doctorsBySubTown);
        //assigned doctor Ids
        $assignedDocId = $doctors->pluck('doc_id');

        //Getting Other hospital staff ids
        $otherStaff = OtherHospitalStaff::whereIn('sub_twn_id',$itinerarySubTownIds)->get();
        //assigned OtherHospital Ids
        $assignedhosid = $otherStaff->pluck('hos_stf_id');

        $allFiveVisits = collect();

        //get chemist wise last 5 visits
        foreach ($assignedChemId as $chm){
            $chemProductiveVisit = ProductiveVisit::with('details','details.product','details.sampling','details.detailing','details.promotion')
            ->where('chemist_id','=',$chm)
            ->orderBy('pro_visit_id','desc')->limit(5)
            ->get();

            $allFiveVisits = $allFiveVisits->concat($chemProductiveVisit);
        }

        //get doctor wise last 5 visits
        foreach ($assignedDocId as $dc){
            $docProductiveVisit = ProductiveVisit::with('details','details.product','details.sampling','details.detailing','details.promotion')
            ->where('doc_id','=',$dc)
            ->orderBy('pro_visit_id','desc')->limit(5)
            ->get();

            $allFiveVisits = $allFiveVisits->concat($docProductiveVisit);
        }

        //get hospital user wise last 5 visits
        foreach ($assignedhosid as $hos){
            $hosProductiveVisit = ProductiveVisit::with('details','details.product','details.sampling','details.detailing','details.promotion')
            ->where('hos_stf_id','=',$hos)
            ->orderBy('pro_visit_id','desc')->limit(5)
            ->get();

            $allFiveVisits = $allFiveVisits->concat($hosProductiveVisit);
        }
        $allFiveVisits->transform(function($pv){
            $pro_id = "";
            $pro_name = "";
            $sampling_reason = "";
            $detailing_reason = "";
            $promotional_reason = "";
            $remark = "";
            $qty = 0;
            foreach ($pv->details as $dt){
                $pro_id = $dt->product_id;
                $pro_name = $dt->product->product_name;
                $sampling_reason = $dt->sampling?$dt->sampling->rsn_name:"";
                $detailing_reason = $dt->detailing?$dt->detailing->rsn_name:"";
                $promotional_reason = $dt->promotion?$dt->promotion->rsn_name:"";
                $remark = $dt->remark;
                $qty = $dt->qty;
            }
            return [
                'visit_id'=>$pv->pro_visit_id,
                'product_id'=>$pro_id,
                'product_name'=>$pro_name,
                'sampling_reason'=>$sampling_reason,
                'detailing_reason'=>$detailing_reason,
                'promotional_reason'=>$promotional_reason,
                'remark'=>$remark,
                'qty'=>$qty
            ];
        });
        return [
            "result"=>true,
            "visit_pro_details"=>$allFiveVisits
        ];
    }

    /**
     * Returning missed visits for a day
     *
     * @param Request $request required day as a string
     * @return Illuminate\Http\JsonResponse
     */
    public function getMissedForDay(Request $request){
        $validation = Validator::make($request->all(),[
            'date'=>'required|date'
        ]);

        if($validation->fails()){
            throw new MediAPIException("Request parameters can not validate",4);
        }

        $user = Auth::user();

        $day = $request->input("date");

        $subTownIds = $this->getTerritoriesByItinerary($user,strtotime($day))->pluck('sub_twn_id')->all();

        $allocatedCustomers = UserCustomer::getByUser($user);

        $allocatedChemistIds = $allocatedCustomers
            ->pluck('chemist_id')
            ->filter(function($id){return !!$id;})
            ->values();
        $allocatedDoctorIds = $allocatedCustomers
            ->pluck('doc_id')
            ->filter(function($id){return !!$id;})
            ->values();

        $productiveCustomers = ProductiveVisit::select(['doc_id','chemist_id','hos_stf_id'])->whereDate('pro_start_time',$day)->where('u_id',$user->getKey())->get();

        $productiveChemistIds = $productiveCustomers
            ->pluck('chemist_id')
            ->filter(function($id){return !!$id;})
            ->all();
        $productiveDoctorIds = $productiveCustomers
            ->pluck('doc_id')
            ->filter(function($id){return !!$id;})
            ->all();
        $productiveStaffIds = $productiveCustomers
            ->pluck('hos_stf_id')
            ->filter(function($id){return !!$id;})
            ->all();

        $unproductiveCustomers = UnproductiveVisit::select(['doc_id','chemist_id','hos_stf_id'])->whereDate('unpro_time',$day)->where('u_id',$user->getKey())->get();

        $unproductiveChemistIds = $unproductiveCustomers
            ->pluck('chemist_id')
            ->filter(function($id){return !!$id;})
            ->all();
        $unproductiveDoctorIds = $unproductiveCustomers
            ->pluck('doc_id')
            ->filter(function($id){return !!$id;})
            ->all();
        $unproductiveStaffIds = $unproductiveCustomers
            ->pluck('hos_stf_id')
            ->filter(function($id){return !!$id;})
            ->all();


        $chemists = Chemist::whereIn('sub_twn_id',$subTownIds)
            ->with('chemist_market_description')
            ->whereIn('chemist_id',$allocatedChemistIds)
            ->whereNotIn('chemist_id',$productiveChemistIds)
            ->whereNotIn("chemist_id",$unproductiveChemistIds)
            ->get();

        $subTownDocIds = DoctorSubTown::whereIn('sub_twn_id',$subTownIds)->whereIn('doc_id',$allocatedDoctorIds)->get()->pluck('doc_id')->all();

        $doctors = Doctor::whereIn('doc_id',$subTownDocIds)
            ->with('doctor_speciality')
            ->whereNotIn('doc_id',$unproductiveDoctorIds)
            ->whereNotIn('doc_id',$productiveDoctorIds)
            ->get();

        $staffs = OtherHospitalStaff::whereIn('sub_twn_id',$subTownIds)
            ->with('hospital_staff_category')
            ->whereNotIn('hos_stf_id',$productiveStaffIds)
            ->whereNotIn('hos_stf_id',$unproductiveStaffIds)
            ->get();

        $doctors->transform(function($doctor){
            return [
                "personId"=>$doctor->getKey(),
                'personName'=>$doctor->doc_name,
                "personSpeciality"=>isset($doctor->doctor_speciality)?$doctor->doctor_speciality->speciality_name:"",
                "personType"=>0
            ];
        });

        $chemists->transform(function($chemist){
            return [
                "personId"=>$chemist->getKey(),
                'personName'=>$chemist->chemist_name,
                "personSpeciality"=>isset($chemist->chemist_market_description)?$chemist->chemist_market_description->chemist_mkd_name:"",
                "personType"=>1
            ];
        });

        $staffs->transform(function($otherHospitalStaff){
            return [
                "personId"=>$otherHospitalStaff->getKey(),
                'personName'=>$otherHospitalStaff->hos_stf_name,
                "personSpeciality"=>isset($otherHospitalStaff->hospital_staff_category)?$otherHospitalStaff->hospital_staff_category->hos_stf_cat_name:"",
                "personType"=>2
            ];
        });

        $concated = $chemists->concat($doctors);

        $concated = $concated->concat($staffs);

        return response()->json([
            'result'=>true,
            'missed_data'=>$concated
        ]);

    }
}
