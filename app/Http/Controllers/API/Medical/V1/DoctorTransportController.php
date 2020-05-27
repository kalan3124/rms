<?php 
namespace App\Http\Controllers\API\Medical\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\MediAPIException;
use \Illuminate\Support\Facades\Auth;
use Validator; 
use App\Models\MrDoctorTransport;

class DoctorTransportController extends Controller{

    public function save(Request $request){
    // Storage::put('/public/docTransport.txt', json_encode($request->all()));

    $user= Auth::user();

    // Checking the request is empty
    if(!$request->has('jsonString'))
        throw new MediAPIException('Some parameters are not found', 5);

    // Decoding the json
    $json_decode = json_decode($request['jsonString'],true);

    // Make a new validation rule
    $validator = Validator::make($json_decode, [
        'doctorId' => 'required',
        'bataId' => 'required',
        'expensesId' => 'required',
        'startMileage' => 'required',
        'endMileage' => 'required',
        'startLat' => 'required',
        'startLon' => 'required',
        'startLocType' => 'required',
        'endLocType' => 'required',
        'endLat' => 'required',
        'endLon' => 'required',
        'startTime' => 'required',
        'endTime' => 'required',
        'app_version'
    ]);

    // Throw an exception if required parameters not supplied
    if ($validator->fails()) 
        throw new MediAPIException($validator->errors()->first(), 4);

    $strt_timestamp = $json_decode['startTime'] / 1000;
    $start_time = date("Y-m-d h:i:s", $strt_timestamp);

    $end_timestamp = $json_decode['startTime'] / 1000;
    $end_time = date("Y-m-d h:i:s", $end_timestamp);

    $appVersion = null;
    if(isset($request['appVersion'])){
        $appVersion = $request['appVersion'];
    }

    $data = [
        'u_id'=>$user->getKey(),
        'doc_id'=>$json_decode['doctorId'],
        'bata_rsn_id'=>$json_decode['bataId'],
        'exp_rsn_id'=>$json_decode['expensesId'],
        'start_time'=>$start_time,
        'end_time'=>$end_time
    ];
    $ck_mrDoctorTransfer = MrDoctorTransport::where($data)
                ->latest()
                ->first();
    if($ck_mrDoctorTransfer){
        throw new MediAPIException('Doctor transport information has been already added', 27);
    }else{

    $mrDoctorTransfer = MrDoctorTransport::create([
        'u_id'=>$user->getKey(),
        'doc_id'=>$json_decode['doctorId'],
        'bata_rsn_id'=>$json_decode['bataId'],
        'exp_rsn_id'=>$json_decode['expensesId'],
        'start_mileage'=>$json_decode['startMileage'],
        'end_mileage'=>$json_decode['endMileage'],
        'start_lat'=>$json_decode['startLat'],
        'start_lon'=>$json_decode['startLon'],
        'start_loc_type'=>$json_decode['startLocType'],
        'end_loc_type'=>$json_decode['endLocType'],
        'end_lat'=>$json_decode['endLat'],
        'end_lon'=>$json_decode['endLon'],
        'start_time'=>$start_time,
        'end_time'=>$end_time,
        'app_version'=>$appVersion
    ]);

    return response()->json([
        "result" => true,
        "message" => "Doctor transport has been successfully added"
    ]);
    }

    }
}