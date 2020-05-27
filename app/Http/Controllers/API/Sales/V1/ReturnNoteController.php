<?php
namespace App\Http\Controllers\API\Sales\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\MediAPIException;
use \Illuminate\Support\Facades\Auth;
use App\Models\SfaReturnNote;

class ReturnNoteController extends Controller{

    public function save(Request $request){

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
        $return = SfaReturnNote::where('u_id', $user->getKey())
            ->count();
        if(!empty($return)){
            $return = $return + 1;
            $rnNumber = 'UN/'.$repType.'/'.$user->id.'/'.$return;
        } else{
            $rnNumber = 'UN/'.$repType.'/'.$user->id.'/1';
        }

        $ckRnNumber = SfaReturnNote::where('rn_no', $rnNumber)
            ->get();
            if(!$ckRnNumber->isEmpty()) {
                return [
                    'result'=>true,
                    "message" => "Return Note Number is Already Exist!"
                ];
                // throw new MediAPIException("Return Note Number is Already Exist!",23);
            }

        $appVersion = null;
        if(isset($request['appVersion'])){
            $appVersion = $request['appVersion'];
        }

        // Java Timestamp = PHP Unix Timestamp * 1000
        $rn_timestamp = $json_decode['time'] / 1000;

        // Formating the unix timestamp to a string
        $rn_time = date("Y-m-d H:i:s", $rn_timestamp);

        // Getting the last attendance record related to user
        $data = [
            'u_id'=> $user->getKey(),
            'chemist_id'=> $json_decode['doc_chem_id'],
            'rn_time'=> $rn_time,
            'is_sheduled'=>$json_decode['is_scheduled']
        ];

        $ck_rnNote = SfaReturnNote::where($data)->get();
        
            if(!$ck_rnNote->isEmpty()){
                return [
                    'result'=>true,
                    "message" => "Return Note Number is Already Exist!"
                ];
                // throw new MediAPIException("Return Note is already exist!",37);
        }else{

            $returnNote = SfaReturnNote::create([
                'rn_no'=>$rnNumber,
                'u_id'=>$user->getKey(),
                'chemist_id'=>$json_decode['doc_chem_id'],
                'is_sheduled'=>$json_decode['is_scheduled'],
                'remark'=>$json_decode['retrun_remark'],
                'sr_availability'=>$json_decode['sr_status'],
                'mr_availability'=>$json_decode['mr_status'],
                'latitude'=>$json_decode['lat'],
                'longitude'=>$json_decode['lon'],
                'rn_time'=>$rn_time,
                'battery_level'=>$json_decode['battery_level'],
                'app_version'=>$appVersion
            ]);
        }

        return [
            'result'=>true,
            "message" => "Return Note has been successfully entered"
        ];
    }
}