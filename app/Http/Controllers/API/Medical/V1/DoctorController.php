<?php 
namespace App\Http\Controllers\API\Medical\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\UserCustomer;
use App\Models\DoctorClass;
use App\Models\DoctorSpeciality;
use App\Models\SubTown;
use App\Models\Institution;
use Validator;
use App\Exceptions\MediAPIException;
use App\Models\MrDoctorCreaton;
use App\Models\User;

use App\Traits\Territory;

class DoctorController extends Controller{

    use Territory;

    public function doctors( Request $request){

        $timestamp = $request->input('timestamp');
        if($timestamp){
            $timestamp = $timestamp/1000;
        }

        $user= Auth::user();
        
        $userCustomers = UserCustomer::getByUser($user,2);
        //select some important key values from Doctor Json
        $userCustomers->transform(function ($userCustomer, $key) use($timestamp) {
            if($timestamp){
                if($userCustomer->created_at->timestamp<=$timestamp){
                    return  null;
                }
            }
            return [
                'doc_id'=>$userCustomer->doctor->doc_id,
                'doc_name'=>$userCustomer->doctor->doc_name,
                'spec_id'=>$userCustomer->doctor->doc_spc_id??0,
                'spec_short_code'=>$userCustomer->doctor->doctor_speciality?$userCustomer->doctor->doctor_speciality->speciality_short_name:"",
                'spec_name'=>$userCustomer->doctor->doctor_speciality?$userCustomer->doctor->doctor_speciality->speciality_name:"",
                'class_id'=>$userCustomer->doctor->doc_class_id??0,
                'class_name'=>$userCustomer->doctor->doctor_class?$userCustomer->doctor->doctor_class->doc_class_name:""
            ];
        });
        
        $userCustomers =$userCustomers->filter(function($product){return !!$product;})->values() ;
        

        return [
            'result'=>true,
            'doctors'=>$userCustomers,
            'count'=>$userCustomers->count()
        ];

    }

    public function doctorClasses(Request $request){

        $timestamp = $request->input('timestamp');
        if($timestamp){
            $timestamp = $timestamp/1000;
        }
        
        $user= Auth::user();

        $doctorClass = DoctorClass::get();

        $doctorClass->transform(function($doc) use($timestamp){
            if($timestamp){
                if($doc->created_at->timestamp<=$timestamp){
                    return  null;
                }
            }

            return [
                'doc_class_id'=>$doc->doc_class_id,
                'doc_class_name'=>$doc->doc_class_name
            ];
        });

        $doctorClass =$doctorClass->filter(function($product){return !!$product;})->values() ;

        return [
            'result'=>true,
            'doctor_class'=>$doctorClass
        ];
    }

    public function doctorSpecifications(Request $request){
        $user= Auth::user();
        $docSpec = DoctorSpeciality::get();

        $timestamp = $request->input('timestamp');
        if($timestamp){
            $timestamp = $timestamp/1000;
        }

        $docSpec->transform(function($docSp) use($timestamp) {

            if($timestamp){
                if($docSp->created_at->timestamp<=$timestamp){
                    return  null;
                }
            }

            return [
                'doc_spc_id'=>$docSp->doc_spc_id,
                'speciality_name'=>$docSp->speciality_name,
                'speciality_short_name'=>$docSp->speciality_short_name
            ];
        });

        $docSpec =$docSpec->filter(function($product){return !!$product;})->values() ;

        return [
            'result'=>true,
            'doc_spec'=>$docSpec
        ];
    }

    public function getSubTowns(Request $request){
        $user= Auth::user();

        $timestamp = $request->input('timestamp');
        if($timestamp){
            $timestamp = $timestamp/1000;
        }

        $SubTown = SubTown::get();

        $allocatedSubTown = $this->getAllocatedTerritories($user);

        $SubTown->transform(function($st)use($allocatedSubTown,$timestamp){

            $allocatedSub = $allocatedSubTown->where('sub_twn_id', $st->sub_twn_id);
            $allocatedSub = $allocatedSub->values();
            $status = FALSE;
            if(!$allocatedSub->isEmpty()){
                $status = TRUE;
            }

            if($timestamp){
                if($st->created_at->timestamp<=$timestamp){
                    return  null;
                }
            }

            return [
                'sub_twn_id'=>$st->sub_twn_id,
                'sub_twn_code'=>$st->sub_twn_code,
                'sub_twn_name'=>$st->sub_twn_name,
                'sub_twn_allocated_status'=>$status
            ];
        });

        $SubTown =$SubTown->filter(function($product){return !!$product;})->values() ;

        return [
            'result'=>true,
            'sub_town'=>$SubTown
        ];
    }

    public function getInstitutions(Request $request){
        $user= Auth::user();

        $timestamp = $request->input('timestamp');
        if($timestamp){
            $timestamp = $timestamp/1000;
        }

        $institution = Institution::get();

        $institution->transform(function($institution) use($timestamp){
            if($timestamp){
                if($institution->created_at->timestamp<=$timestamp){
                    return  null;
                }
            }

            return [
                'ins_id'=>$institution->ins_id,
                'ins_name'=>$institution->ins_name,
                'ins_short_name'=>$institution->ins_short_name,
                'ins_code'=>$institution->ins_code,
                'ins_address'=>$institution->ins_address
            ];
        });
        
        $institution =$institution->filter(function($product){return !!$product;})->values() ;

        return [
            'result'=>true,
            'inc_details'=>$institution
        ];
    }

    public function saveDoctors(Request $request){
        Storage::put('/public/saveDoctors.txt', json_encode($request->all()));
        
        $user= Auth::user();

        // Checking the request is empty
        if(!$request->has('jsonString'))
        throw new MediAPIException('Some parameters are not found', 5);

        // Decoding the json
        $json_decode = json_decode($request['jsonString'], true);

        // Make a new validation rule
        $validator = Validator::make($json_decode, [
            'doctor_name' => 'required',
            'sub_town_id' => 'required',
            'doc_spec_id' => 'required',
            'gender_id' => 'required'
        ]);
        // Throw an exception if required parameters not supplied
        if ($validator->fails()) 
            throw new MediAPIException($validator->errors()->first(), 4);

        // Java Timestamp = PHP Unix Timestamp * 1000
        $timestamp = $json_decode['create_time'] / 1000;
        // Formating the unix timestamp to a string
        $doc_time = date("Y-m-d h:i:s", $timestamp);

        $appVersion = null;
        if(isset($request['appVersion'])){
            $appVersion = $request['appVersion'];
        }

        $data = [
            'u_id'=>$user->getKey(),
            'added_date'=>$doc_time
        ];
        $ck_mr_doctors = MrDoctorCreaton::where($data)
                    ->latest()
                    ->first();
        if($ck_mr_doctors){
            return response()->json([
                "result" => true,
                "message" => "Doctor information has been already added"
            ]);
        }else{
        $MrDoctorCreaton = MrDoctorCreaton::create([
            'u_id'=>$user->getKey(),
            'doc_code'=>$json_decode['code'],
            'doc_name'=>$json_decode['doctor_name'],
            'slmc_no'=>$json_decode['slmc_number'],
            'phone_no'=>$json_decode['phone_no'],
            'mobile_no'=>$json_decode['mobile_no'],
            'gender'=>$json_decode['gender_id'],
            'date_of_birth'=>$json_decode['dob'],
            'sub_twn_id'=>$json_decode['sub_town_id'],
            'doc_class_id'=>($json_decode['doc_class_id']!='-1')?$json_decode['doc_class_id']:NULL,
            'doc_spc_id'=>($json_decode['doc_spec_id']!='-1')?$json_decode['doc_spec_id']:NULL,
            'ins_id'=>($json_decode['institution_id']!='-1')?$json_decode['institution_id']:NULL,
            'added_date'=>$doc_time,
            'app_version'=>$appVersion
        ]);
        }

        return response()->json([
            "result" => true,
            "message" => "doctor has been successfully entered"
        ]);
    }
}