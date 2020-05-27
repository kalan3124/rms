<?php 
namespace App\Http\Controllers\API\Sales\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\MediAPIException;
use App\Models\SfaUnproductiveVisit;
use App\Models\UserAttendance;

class UnproductiveController extends Controller{

    public function save(Request $request){

        // Checking the request is empty
        if(!$request->has('jsonString'))
            throw new MediAPIException('Some parameters are not found', 5);

        // Decoding the json
        $json_decode = json_decode($request['jsonString'], true);

        // Getting the logged user
        $user = Auth::user();

        //get userType
        $repType = null;
        if(config('shl.sales_rep_type')==$user->u_tp_id){
            $repType = 'SR';
        }

        //generate unproductive no
        $unpro = SfaUnproductiveVisit::where('u_id', $user->getKey())
            ->count();
        if(!empty($unpro)){
            $unpro = $unpro + 1;
            $unNumber = 'UN/'.$repType.'/'.$user->id.'/'.$unpro;
        } else{
            $unNumber = 'UN/'.$repType.'/'.$user->id.'/1';
        }

        $ckUnNumber = SfaUnproductiveVisit::where('un_visit_no', $unNumber)
            ->get();
            if(!$ckUnNumber->isEmpty()) {
                // throw new MediAPIException("Unproductive Number Already Exist!",23);
                return [
                    'result'=>true,
                    "message" => "Unproductive Information is already exist!"
                ];
            }

        // Java Timestamp = PHP Unix Timestamp * 1000
        $timestamp = $json_decode['time'] / 1000;
        // Formating the unix timestamp to a string
        $unpro_time = date("Y-m-d h:i:s", $timestamp);

        $appVersion = null;
        if(isset($request['appVersion'])){
            $appVersion = $request['appVersion'];
        }

        $data = [
            'u_id'=>$user->getKey(),
            'chemist_id'=>$json_decode['doc_chem_id'],
            'is_sheduled'=>$json_decode['is_scheduled'],
            'unpro_time'=>$unpro_time
        ];

        $ck_unproductive = SfaUnproductiveVisit::where($data)->get();
            if(!$ck_unproductive->isEmpty()){
                // throw new MediAPIException("Unproductive Information is already exist!",37);
                return [
                    'result'=>true,
                    "message" => "Unproductive Information is already exist!"
                ];
        }else{

            $unproductive = SfaUnproductiveVisit::create([
                'un_visit_no'=>$unNumber,
                'u_id'=>$user->getKey(),
                'chemist_id'=>$json_decode['doc_chem_id'],
                'is_sheduled'=>$json_decode['is_scheduled'],
                'rsn_id'=>$json_decode['unproductive_reason_id'],
                'latitude'=>$json_decode['lat'],
                'longitude'=>$json_decode['lon'],
                'unpro_time'=>$unpro_time,
                'battery_level'=>$json_decode['battery_level'],
                'app_version'=>$appVersion
            ]);
        }

        return [
            'result'=>true,
            "message" => "Unproductive has been successfully entered"
        ];
    }

    public function getDailyUnPro(Request $request){
        $user = Auth::user();

        $attendance = UserAttendance::where('u_id', $user->getKey())
                    ->latest()
                    ->first();

        if(!$attendance->checkout_status){
            $unpro = SfaUnproductiveVisit::with('chemist')->where('u_id',$user->getKey())->where('unpro_time','>',date('Y-m-d H:i:s',strtotime($attendance->check_in_time)))->get();

            $unpro->transform(function($val){
                return[
                    "sfa_un_id"=> $val->sfa_un_id,
                    "un_visit_no"=> $val->un_visit_no,
                    "u_id"=> $val->u_id,
                    "chemist_id"=> $val->chemist_id,
                    "is_sheduled"=> $val->is_sheduled,
                    "rsn_id"=> $val->rsn_id,
                    "latitude"=> $val->latitude,
                    "longitude"=> $val->longitude,
                    "unpro_time"=> $val->unpro_time,
                    "battery_level"=> $val->battery_level,
                    "app_version"=> $val->app_version,
                    "chemist" => $val->chemist
                ];
            });
        }

        return [
            'result'=>true,
            "unproductive" => isset($unpro)?$unpro:null
        ];
    }
}