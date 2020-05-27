<?php 
namespace App\Http\Controllers\API\Medical\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\MediAPIException;
use App\Models\UnproductiveVisit;

class UnproductiveController extends Controller{

    public function save(Request $request){
        Storage::put('/public/unproductives.txt', json_encode($request->all()));

        // Checking the request is empty
        if(!$request->has('jsonString'))
            throw new MediAPIException('Some parameters are not found', 5);
            

        // Decoding the json
        $json_decode = json_decode($request['jsonString'], true);

        if(!$json_decode['visit_place_id']) throw new MediAPIException("Please Provide a visit place.",20);

        // Getting the logged user
        $user = Auth::user();
        //get userType
        $repType = null;
        
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type')
        ])){
            $repType = 'MR';
        } else if(config('shl.field_manager_type')==$user->u_tp_id){
            $repType = 'FM';
        }

        //generate unproductive no
        $unpro = UnproductiveVisit::where('u_id', $user->getKey())
            ->count();
        if(!empty($unpro)){
            $unpro = $unpro + 1;
            $unNumber = 'UN/'.$repType.'/'.$user->id.'/'.$unpro;
        } else{
            $unNumber = 'UN/'.$repType.'/'.$user->id.'/1';
        }
        $ckUnNumber = UnproductiveVisit::where('un_visit_no', $unNumber)
            ->get();
            if(!$ckUnNumber->isEmpty()) throw new MediAPIException("Unproductive Number Already Exist!",23);

        // Java Timestamp = PHP Unix Timestamp * 1000
        $timestamp = $json_decode['time'] / 1000;

        // Formating the unix timestamp to a string
        $unpro_time = date("Y-m-d h:i:s", $timestamp);

        //checking visit type 0-Doctor, 1-Chemist
        $chemist = null;
        $doctor = null;
        $otherHospitalStaff = null;
        if($json_decode['visit_type_id']== 0){
            $doctor = $json_decode['doc_chem_id'];
        }else if($json_decode['visit_type_id']== 1){
            $chemist = $json_decode['doc_chem_id'];
        }else {
            $otherHospitalStaff = $json_decode['doc_chem_id'];
        }
        $appVersion = null;
        if(isset($request['appVersion'])){
            $appVersion = $request['appVersion'];
        }
        $unproductive = UnproductiveVisit::create([
            'un_visit_no'=>$unNumber,
            'doc_id'=>$doctor,
            'chemist_id'=>$chemist,
            'u_id'=>$user->getKey(),
            'visit_type'=>$json_decode['visit_type_id'],
            'is_shedule'=>$json_decode['is_sheduled'],
            'shedule_id'=>$json_decode['shedule_id'],
            'reason_id'=>$json_decode['unproductive_reason_id'],
            'btry_lvl'=>$json_decode['battery_level'],
            'lat'=>$json_decode['lat'],
            'lon'=>$json_decode['lon'],
            'unpro_time'=>$unpro_time,
            'visited_place'=>$json_decode['visit_place_id'],
            'app_version'=> $appVersion,
            'hos_stf_id'=>$otherHospitalStaff
        ]);

        return [
            'result'=>true,
            "message" => "Unproductive has been successfully entered"
        ];
    }
}