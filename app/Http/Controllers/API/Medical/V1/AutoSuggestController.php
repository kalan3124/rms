<?php
namespace App\Http\Controllers\API\Medical\V1;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Traits\Territory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\MediAPIException;
use App\Models\Chemist;
use App\Models\OtherHospitalStaff;
use App\Models\UserCustomer;

class AutoSuggestController extends Controller {
    use Territory;
    
    public function searchOtherCustomers(Request $request){
        $validator = Validator::make($request->all(),[
            'keyword'=>'required|min:4'
        ]);

        if($validator->fails()){
            throw new MediAPIException("Person name validation failed. Person name should be",32);
        }

        $keyword = $request->input('keyword');

        $user = Auth::user();

        $subTowns = $this->getAllocatedTerritories($user)->pluck('sub_twn_id')->all();

        $doctors = Doctor::with('doctor_speciality')
            ->join('doctor_sub_towns','doctors.doc_id','doctor_sub_towns.doc_id')
            ->whereNotIn('sub_twn_id',$subTowns)
            ->where(function($query)use($keyword){
                $query->orWhere('doc_name','LIKE',"%$keyword%");
                $query->orWhere('doc_code','LIKE',"%$keyword%");
            })->get();

        $doctors->transform(function($doctor){
            return [
                "personId"=>$doctor->getKey(),
                'personName'=>$doctor->doc_name,
                "personSpeciality"=>isset($doctor->doctor_speciality)?$doctor->doctor_speciality->speciality_name:"",
                "personType"=>0
            ];
        });

        $chemists = Chemist::with('chemist_market_description')->whereNotIn('sub_twn_id',$subTowns)->where(function($query)use($keyword){
            $query->orWhere('chemist_name','LIKE',"%$keyword%");
            $query->orWhere('chemist_code','LIKE',"%$keyword%");
        })->get();

        $chemists->transform(function($chemist){
            return [
                "personId"=>$chemist->getKey(),
                'personName'=>$chemist->chemist_name,
                "personSpeciality"=>isset($chemist->chemist_market_description)?$chemist->chemist_market_description->chemist_mkd_name:"",
                "personType"=>1
            ];
        });

        $allocatedCustomers = UserCustomer::where('u_id',$user->getKey())
            ->where(function($query)use($doctors,$chemists){
                $query->orWhereIn('doc_id',$doctors->pluck('personId')->all());
                $query->orWhereIn('chemist_id',$chemists->pluck('personId')->all());
            })
            ->get();

        $allocatedDoctorIds = $allocatedCustomers->pluck('doc_id')->filter(function($doc_id){return !!$doc_id;})->all();
        $allocatedChemistIds = $allocatedCustomers->pluck('chemist_id')->filter(function($doc_id){return !!$doc_id;})->all();

        $newChemists = $chemists->filter(function($chemist)use($allocatedChemistIds){
            return !in_array($chemist['personId'],$allocatedChemistIds);
        });

        $newDoctors = $doctors->filter(function($doctor)use($allocatedDoctorIds){
            return !in_array($doctor['personId'],$allocatedDoctorIds);
        });
        

        $merged = $newDoctors->concat($newChemists);

        $otherHospitalStaffs = OtherHospitalStaff::with('hospital_staff_category')->whereNotIn('sub_twn_id',$subTowns)->where(function($query)use($keyword){
            $query->orWhere('hos_stf_name','LIKE',"%$keyword%");
            $query->orWhere('hos_stf_code','LIKE',"%$keyword%");
        })->get();

        $otherHospitalStaffs->transform(function($otherHospitalStaff){
            return [
                "personId"=>$otherHospitalStaff->getKey(),
                'personName'=>$otherHospitalStaff->hos_stf_name,
                "personSpeciality"=>isset($otherHospitalStaff->hospital_staff_category)?$otherHospitalStaff->hospital_staff_category->hos_stf_cat_name:"",
                "personType"=>2
            ];
        });

        $merged = $merged->concat($otherHospitalStaffs);

        return response()->json([
            'result'=>true,
            'call_od_data'=>$merged->values()
        ]);
    }
}