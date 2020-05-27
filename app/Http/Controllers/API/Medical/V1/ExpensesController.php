<?php 
namespace App\Http\Controllers\API\Medical\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\MediAPIException;
use Illuminate\Support\Facades\Storage;
use \Illuminate\Support\Facades\Auth;
use Validator; 
use App\Models\Expenses;
use App\Models\StationMileage;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\Team;
use App\Models\TeamUser;
use App\Traits\Territory;
use App\Models\UserAttendance;
use App\Models\VehicleTypeRate;

class ExpensesController extends Controller{

    use Territory;

    public function expenses(Request $request){

        Storage::put('/public/expenses.txt', json_encode($request->all()));

        $user= Auth::user();
        // Checking the request is empty
        if(!$request->has('jsonString'))
            throw new MediAPIException('Some parameters are not found', 5);

        // Decoding the json
        $json_decode = json_decode($request['jsonString'],true);
        
        // Make a new validation rule
        $validator = Validator::make($json_decode, [
            'expId' => 'required',
            'expAmount' => 'required',
            'expTime' => 'required'
        ]);

        // Throw an exception if required parameters not supplied
        if ($validator->fails()) 
            throw new MediAPIException($validator->errors()->first(), 4);

        // Java Timestamp = PHP Unix Timestamp * 1000
        $timestamp = $json_decode['expTime'] / 1000;

        if(!isset($json_decode['expTime'])){
            $timestamp = time();
        }

        // Formating the unix timestamp to a string
        $expense_time = date("Y-m-d h:i:s", $timestamp);

        $teamUser  = TeamUser::with('team')->where('u_id',$user->id)->first();
        // Field Manager
        $team = Team::where('fm_id',$user->id)->first();

        if(isset($teamUser)&&$teamUser->team){
            $team = $teamUser->team;
        }

        if(! $team){
            throw new MediAPIException("You haven't team.",21);
        }

        $year = date("Y",$timestamp);
        $month = date("m",$timestamp);

        if($year<=date("Y")&&$month<=date('m')-2){
            throw new MediAPIException("This service is temporarily unavailable for the given date.",37);
        }
        
        if($year<=date("Y")&&$month<=date('m')-1&&strtotime($team->tm_exp_block_date)<=time()){
            throw new MediAPIException("This service is temporarily unavailable for the given date.",37);
        }

        $appVersion = null;
        if(isset($request['appVersion'])){
            $appVersion = $request['appVersion'];
        }

        /** Check json is whether expenses or station mileage */
        if($json_decode['expId']!='-1'){

            $imgUrl = null;
            if(isset($json_decode['image_file'])){
            // Bese 64 image conversion
            $image = $json_decode['image_file'];
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = str_random(10).'.'.'png';
            Storage::put('/public/expensesImage/'.date("Y").'/'.date("m").'/'.date("d").'/'.$imageName,base64_decode($image));

            $imgUrl = '/storage/expensesImage/'.date("Y").'/'.date("m").'/'.date("d").'/'.$imageName;
            }

            $expDate = $json_decode['expDate'].' '.date('h:i:s');
            
            $data = [
                'u_id'=>$user->getKey(),
                'rsn_id'=>$json_decode['expId'],
                'exp_amt'=>$json_decode['expAmount'],
                'exp_date'=>$expDate
            ];
            $ck_expenses = Expenses::where($data)
                        ->latest()
                        ->first();
            if($ck_expenses){
                return response()->json([
                    "result" => true,
                    "message" => "Expenses information has been already added"
                ]);
                // throw new MediAPIException('Expenses information has been already added', 25);
            }else{

            $vehicleTypeRate = VehicleTypeRate::where('u_tp_id',$user->u_tp_id)
                ->where('vht_id',$user->vht_id)
                ->whereDate('vhtr_srt_date','<=',$expDate)
                ->latest()
                ->first();

            $expenses = Expenses::create([
                'u_id'=>$user->getKey(),
                'rsn_id'=>$json_decode['expId'],
                'exp_amt'=>$json_decode['expAmount'],
                'exp_remark'=>$json_decode['expRemark'],
                'image_url'=>$imgUrl,
                'exp_date'=>$expDate,
                'app_version'=>$appVersion,
                'vhtr_rate'=>$vehicleTypeRate?$vehicleTypeRate->vhtr_rate:0
            ]);

            return response()->json([
                "result" => true,
                "message" => "expenses has been successfully entered"
            ]);

            }
        } else {
            $expDate = $json_decode['expDate'];
            /** INSERT STATION MILEAGE */
            $data = [
                'u_id'=>$user->getKey(),
                'stm_date'=>$expense_time,
                'exp_date'=>$expDate
            ];
            $ck_station = StationMileage::where($data)
                        ->latest()
                        ->first();

            if($ck_station){
                return response()->json([
                    "result" => true,
                    "message" => "Station mileage information has been already added"
                ]);
            } else {
                $exist = StationMileage::where('u_id',$user->getKey())->whereDate('exp_date',$expDate)->count();

                if($exist)
                    throw new MediAPIException("Station mileage has already exists for the day.",33);

                $vehicleTypeRate = VehicleTypeRate::where('u_tp_id',$user->u_tp_id)
                    ->where('vht_id',$user->vht_id)
                    ->whereDate('vhtr_srt_date','<=',$expDate)
                    ->latest()
                    ->first();

                $station = StationMileage::create([
                    'u_id'=>$user->getKey(),
                    'exp_amt'=>$json_decode['expAmount'],
                    'exp_remark'=>$json_decode['expRemark'],
                    'stm_date'=>$expense_time,
                    'app_version'=>$appVersion,
                    'exp_date'=>$expDate,
                    'vhtr_rate'=>$vehicleTypeRate?$vehicleTypeRate->vhtr_rate:0
                ]);

                return response()->json([
                    "result" => true,
                    "message" => "station mileage has been successfully entered"
                ]);
            }
        }

    }

    public function hasForDay(Request $request){
        $validation = Validator::make($request->all(),[
            'selectedDate'=>'required|date'
        ]);

        if($validation->fails()){
            throw new MediAPIException("Invalid request supplied. Please try again.",4);
        }

        $user = Auth::user();

        $expenses = StationMileage::where('u_id',$user->getKey())->whereDate('exp_date',$request->input('selectedDate'))->count();

        if($expenses){
            throw new MediAPIException("Station mileage has already exists for the day.",33);
        } else {
            return response()->json([
                'result'=>true,
                'message'=>"Success."
            ]);
        }
    }

    public function getDateDetails(Request $request){
        $validation = Validator::make($request->all(),[
            'date'=>'required|date'
        ]);

        if($validation->fails()){
            throw new MediAPIException('Some parameters are not found', 5);
        }

        $user = Auth::user();

        $date = strtotime($request->input('date'));

        $year = date('Y',$date);
        $month = date('m',$date);
        $day = date('d',$date);

        $teamUser  = TeamUser::with('team')->where('u_id',$user->id)->first();
        $team = Team::where('fm_id',$user->id)->first();

        if(isset($teamUser)&&$teamUser->team){
            $team = $teamUser->team;
        }

        if(! $team){
            throw new MediAPIException("You haven't team.",21);
        }

        if($year<=date("Y")&&$month<=date('m')-2){
            throw new MediAPIException("This service is temporarily unavailable for the given date.",37);
        }

        if($year<=date("Y")&&$month<=date('m')-1&&strtotime($team->tm_exp_block_date)<=time()){
            throw new MediAPIException("This service is temporarily unavailable for the given date.",37);
        }

        $itinerary = Itinerary::where('i_year',$year)
            ->where('i_month',$month)
            ->where(function($query)use($user){
                $query->orWhere('rep_id',$user->getKey());
                $query->orWhere('fm_id',$user->getKey());
            })
            ->latest()
            ->first();

        $with = [
            'bataType',
            'joinFieldWorker',
            'additionalRoutePlan',
            'additionalRoutePlan.bataType',
            'itineraryDayTypes',
            'itineraryDayTypes.dayType',
            'standardItineraryDate',
            'standardItineraryDate.bataType',
            'changedItineraryDate',
            'changedItineraryDate.bataType',
        ];

        if(isset($itinerary)){
            $itineraryDate = ItineraryDate::with($with)->where('i_id',$itinerary->getKey())
                ->where('id_date',$day)
                ->first();

            if(!isset($itineraryDate)){
                throw new MediAPIException("Can not find an itinerary for you or your JFW on this day!",30);
            }

            $stationMileage = StationMileage::whereDate('exp_date',date('Y-m-d',$date))->where('u_id',$user->getKey())->first();

            if($stationMileage){
                throw new MediAPIException("Station mileage has already exists for the day.",33);
            }

            $attendanceForDay = UserAttendance::whereDate('check_in_time',date('Y-m-d',$date))->where('u_id',$user->getKey())->first();
            if(isset($attendanceForDay)&&isset($attendanceForDay['check_out_time'])){
                throw new MediAPIException("This day is successfully executed.",36);
                
            } else if($attendanceForDay){
                $attendanceForDay = 1;
            } else {
                $attendanceForDay = 0;
            }

            $dateType = 2;
            $mileage = 0.00;
            $bataType = null;

            if(isset($itineraryDate->changedItineraryDate)&&isset($itineraryDate->changedItineraryDate->idc_aprvd_at)){

                $dateType = 7;
                $mileage = $itineraryDate->changedItineraryDate->idc_mileage;
                $bataType = isset($itineraryDate->changedItineraryDate->bataType)?$itineraryDate->changedItineraryDate->bataType:$bataType;

            }else if(isset($itineraryDate->standardItineraryDate)){
                $dateType = 3;
                $mileage = $itineraryDate->standardItineraryDate->sid_mileage;
                $bataType = isset($itineraryDate->standardItineraryDate->bataType)?$itineraryDate->standardItineraryDate->bataType:$bataType;

            }else if(isset($itineraryDate->additionalRoutePlan)){
                $dateType=4;
                $mileage = $itineraryDate->additionalRoutePlan->arp_mileage;
                $bataType = isset($itineraryDate->additionalRoutePlan->bataType)?$itineraryDate->additionalRoutePlan->bataType:$bataType;

            } else if(isset($itineraryDate->joinFieldWorker)){
                $dateType = 5;
                $mileage = $itineraryDate->id_mileage;
                $bataType = isset($itineraryDate->bataType)?$itineraryDate->bataType:$bataType;
            } else {
                $mileage = $itineraryDate->id_mileage;
                $bataType = isset($itineraryDate->bataType)?$itineraryDate->bataType:$bataType;
            }

            if(isset($itineraryDate->joinFieldWorker)){
                $dateType = 5;
            }

            $dayTypes = $itineraryDate->itineraryDayTypes->filter(function($itinerarDayType){
                return !!$itinerarDayType->dayType;
            });
            
            $dayTypes = $dayTypes->map(function($itineraryDayType){
                return [
                    "name"=>$itineraryDayType->dayType->dt_name,
                    "id"=>$itineraryDayType->dayType->dt_id
                ];
            });

            $bataType = $bataType?[
                "id"=>$bataType->getKey(),
                "name"=>$bataType->bt_name
            ]:[
                "id"=>0,
                "name"=>""
            ];


            return response()->json([
                'result'=>true,
                'expensesDay'=>[
                    "date"=>$itineraryDate->id_date,
                    'types'=>$dayTypes->values(),
                    "dateType"=>$dateType,
                    "mileage"=>$mileage,
                    "bataType"=>$bataType,
                    'attendanceType'=>$attendanceForDay
                ]
            ]);

        } else {
            throw new MediAPIException("Can not find an itinerary for you or your JFW!",29);
        }
    }
}