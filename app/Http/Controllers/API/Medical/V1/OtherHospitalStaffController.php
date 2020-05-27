<?php 
namespace App\Http\Controllers\API\Medical\V1;

use App\Http\Controllers\Controller;
use App\Models\OtherHospitalStaff;
use App\Models\SubTown;
use \Illuminate\Support\Facades\Auth;
use App\Traits\Territory;

class OtherHospitalStaffController extends controller{

    use Territory;

    public function otherHospitalStaff(){

        $user= Auth::user();

        $itineraryTowns=$this->getTerritoriesByItinerary($user);

        $itinerarySubTownIds = [];
        
        $itinerarySubTownIds = $itineraryTowns->pluck('sub_twn_id');
        //Get Other Hospital Staff Details
        $OtherHospitalStaff = OtherHospitalStaff::with(['hospital_staff_category','sub_town','institution'])->whereIn('sub_twn_id',$itinerarySubTownIds->all())->get();
        $OtherHospitalStaff->transform(function ($OtherHospitalStaff) {
            return [
                'hos_staff_id'=>$OtherHospitalStaff->hos_stf_id,
                'hos_staff_name'=>$OtherHospitalStaff->hos_stf_name,
                'hos_staff_cat_id'=>isset($OtherHospitalStaff->hospital_staff_category)?$OtherHospitalStaff->hospital_staff_category->hos_stf_cat_id:0,
                'hos_staff_category'=>isset($OtherHospitalStaff->hospital_staff_category)?$OtherHospitalStaff->hospital_staff_category->hos_stf_cat_name:"",
                'sub_twn_id'=>$OtherHospitalStaff->sub_town->sub_twn_id,
                'sub_twn_name'=>$OtherHospitalStaff->sub_town->sub_twn_name,
                'institution_id'=>(isset($OtherHospitalStaff->institution))?$OtherHospitalStaff->institution->ins_id:0,
                'institution'=>isset($OtherHospitalStaff->institution)?$OtherHospitalStaff->institution->ins_name:""
            ];
        });
        return [
            "result"=>true,
            "otherStaff"=>$OtherHospitalStaff,
            'count'=>$OtherHospitalStaff->count()
        ];
    }
}