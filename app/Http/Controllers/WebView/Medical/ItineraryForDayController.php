<?php
namespace App\Http\Controllers\WebView\Medical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\WebViewException;
use App\Traits\Territory;
use Illuminate\Support\Facades\Auth;
use App\Models\Chemist;
use App\Models\UserCustomer;
use App\Models\DoctorSubTown;
use App\Models\OtherHospitalStaff;
use App\Models\ProductiveVisit;
use App\Models\UnproductiveVisit;

class ItineraryForDayController extends Controller {

    use Territory;

    public function index(Request $request){
        $validations = Validator::make($request->all(),[
            'date'=>'required|date'
        ]);

        if($validations->fails()){
            throw new WebViewException("Can not validate your request.");
        }

        $date = $request->input('date');

        $user = Auth::user();

        try{
            $subTowns = $this->getTerritoriesByItinerary($user,strtotime($date));
        } catch(\Throwable $exception) {
            $subTowns = collect();
        }
        
        $subTownIds = $subTowns->pluck('sub_twn_id')->all();

        $allocatedCustomers = UserCustomer::getByUser($user);

        $allocatedChemistIds = $allocatedCustomers->pluck('chemist_id')->filter(function($chemistId){
            return !!$chemistId;
        })->all();

        $allocatedDoctorIds = $allocatedCustomers->pluck('doc_id')->filter(function($doctorId){
            return !!$doctorId;
        })->all();

        $chemists = Chemist::with('chemist_market_description')->whereIn('sub_twn_id',$subTownIds)->whereIn('chemist_id',$allocatedChemistIds)->get();

        // Getting and formating doctors
        $doctors = DoctorSubTown::whereIn('sub_twn_id',$subTownIds)->whereIn('doc_id',$allocatedDoctorIds)->with(['doctor','doctor.doctor_speciality'])->get();

        $doctors->transform(function($doctorSubTown){
            if(!$doctorSubTown->doctor) return null;

            return $doctorSubTown->doctor;
        });

        $doctors = $doctors->filter(function($doctor){return !!$doctor;});

        $otherHospitalStaffs = OtherHospitalStaff::with('hospital_staff_category')->whereIn('sub_twn_id',$subTownIds)->get();

        $productives = ProductiveVisit::where('u_id',$user->getKey())->where(function($query)use($doctors,$chemists,$otherHospitalStaffs){
            $query->orWhereIn('doc_id',$doctors->pluck('doc_id')->all());
            $query->orWhereIn('chemist_id',$chemists->pluck('chemist_id')->all());
            $query->orWhereIn('hos_stf_id',$otherHospitalStaffs->pluck('hos_stf_id')->all());
        })->whereDate('pro_start_time',$date)->get();
    
        $unproductives = UnproductiveVisit::where('u_id',$user->getKey())->where(function($query)use($doctors,$chemists,$otherHospitalStaffs){
            $query->orWhereIn('doc_id',$doctors->pluck('doc_id')->all());
            $query->orWhereIn('chemist_id',$chemists->pluck('chemist_id')->all());
            $query->orWhereIn('hos_stf_id',$otherHospitalStaffs->pluck('hos_stf_id')->all());
        })->whereDate('unpro_time',$date)->get();

        $doctors->transform(function($doctor)use($productives,$unproductives){
            $productive = $productives->where('doc_id',$doctor->getKey())->first();
            $unproductive = $unproductives->where('doc_id',$doctor->getKey())->first();

            return [
                "personId"=>$doctor->getKey(),
                'personName'=>$doctor->doc_name,
                "personSpeciality"=>isset($doctor->doctor_speciality)?$doctor->doctor_speciality->speciality_name:"",
                "personType"=>0,
                "status"=>$productive?1:($unproductive?-1:0)
            ];
        });

        $chemists->transform(function($chemist)use($productives,$unproductives){
            $productive = $productives->where('chemist_id',$chemist->getKey())->first();
            $unproductive = $unproductives->where('chemist_id',$chemist->getKey())->first();

            return [
                "personId"=>$chemist->getKey(),
                'personName'=>$chemist->chemist_name,
                "personSpeciality"=>isset($chemist->chemist_market_description)?$chemist->chemist_market_description->chemist_mkd_name:"",
                "personType"=>1,
                "status"=>$productive?1:($unproductive?-1:0)
            ];
        });

        $otherHospitalStaffs->transform(function($otherHospitalStaff)use($productives,$unproductives){
            $productive = $productives->where('hos_stf_id',$otherHospitalStaff->getKey())->first();
            $unproductive = $unproductives->where('hos_stf_id',$otherHospitalStaff->getKey())->first();

            return [
                "personId"=>$otherHospitalStaff->getKey(),
                'personName'=>$otherHospitalStaff->hos_stf_name,
                "personSpeciality"=>isset($otherHospitalStaff->hospital_staff_category)?$otherHospitalStaff->hospital_staff_category->hos_stf_cat_name:"",
                "personType"=>2,
                "status"=>$productive?1:($unproductive?-1:0)
            ];
        });

        $merged = $doctors->concat($chemists);
        $merged = $merged->concat($otherHospitalStaffs);

        $sorted = $merged->sortBy('personName');

        return view('WebView/Medical.itinerary_for_day',[
            'customers'=>$sorted
        ]);
        
    }
}