<?php

namespace App\Http\Controllers\API\Distributor\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\DisAPIException;
use \Illuminate\Support\Facades\Auth;
use App\Models\UserAttendance;
use Validator;

class UserAttendanceController extends Controller{
     /**
     * Markign users attendance
     * 
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     * @throws DisAPIException
     */

     public function attendance(Request $request){
          // Checking the request is empty
        if(!$request->has('attendance'))
               throw new DisAPIException('Some parameters are not found', 5);

          // Decoding the json
          $json_decode = json_decode($request['attendance'], true);

          // Getting the logged user
          $user = Auth::user();

          // Make a new validation rule
          $validator = Validator::make($json_decode, [
               'type' => 'required',
               'lat' => 'required',
               'lon' => 'required',
               'time' => 'required',
               'mileage' => 'required',
               'batteryLevel' => 'required',
               'locationType' => 'required'
          ]);

          // Throw an exception if required parameters not supplied
          if ($validator->fails()) 
               throw new DisAPIException($validator->errors()->first(), 4);

          // Java Timestamp = PHP Unix Timestamp * 1000
          $timestamp = $json_decode['time'] / 1000;

          // Formating the unix timestamp to a string
          $attend_time = date("Y-m-d H:i:s", $timestamp);

          // Getting the latest attendance record related to the logged user
          $lastAttendance = UserAttendance::where('u_id', $user->getKey())
               ->latest()
               ->first();
          
          // type:- 1= Checking, 2= Checkout
          if ($json_decode['type'] == 1) {

               // Throwing an exception if latest day begin has not a day end
               if($lastAttendance&&!$lastAttendance->isDayEnded()) throw new DisAPIException("Day begin has been already exist!",6);        

               try{
                    // Creating a record
                    $attendance = UserAttendance::create([
                         'u_id' => $user->getKey(),
                         'check_in_lat' => $json_decode['lat'],
                         'check_in_lon' => $json_decode['lon'],
                         'check_in_time' => $attend_time,
                         'check_in_mileage' => $json_decode['mileage'],
                         'check_in_battery' => $json_decode['batteryLevel'],
                         'check_in_loc_type' => $json_decode['locationType'],
                         'app_version'=>$request->input('appVersion','uknown')
                    ]);
               
                    // Returning a positive feedback
                    return response()->json([
                         "result" => true,
                         "message" => "Day begin has been successfully entered"
                    ]);
                    
               } catch(\Exception $e){
                    throw new DisAPIException("Day begin has been already exist!",6);
               }
          } else {

               // Throw an error if can not find one
               if(!$lastAttendance) throw new DisAPIException("Can not find a day begin related to user",7);

               // Throw an error if already day ended
               // if($lastAttendance->isDayEnded()) throw new DisAPIException("Already day ended!",8);
               if($lastAttendance->isDayEnded()){
                    return[
                        'result' => true,
                        'message' => 'Already day ended!'
                    ];
               }

               // Updating the day ending details
               $lastAttendance->update([
                    'check_out_lat' => $json_decode['lat'],
                    'check_out_lon' => $json_decode['lon'],
                    'check_out_time' => $attend_time,
                    'check_out_mileage' => $json_decode['mileage'],
                    'check_out_battery' => $json_decode['batteryLevel'],
                    'check_out_loc_type' => $json_decode['locationType'],
                    'checkout_status' => 1
               ]);

               return response()->json([
                    "result" => true,
                    "message" => "day end has been successfully entered"
               ]);
          }
     }
     /**
     * Send attendance details to app when login
     * 
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     * @throws DisAPIException
     */
    public function dayStatus (Request $request) {
         // Getting logged user
        $user = Auth::user(); 

        // Getting the last attendance record related to user
        $attendance = UserAttendance::where('u_id', $user->getKey())
                    ->latest()
                    ->first();

        // throw an error if not exists any record
        if(!$attendance)
            throw new DisAPIException("Can not find a attendance related to user",9);

        if(!$attendance->checkout_status){
            // If not day ended yet
            $timestamp = $attendance->check_in_time->timestamp*1000;
            $attendance_arr = [
                'type'=> 1,
                'lat'=>$attendance->check_in_lat,
                'lon'=>$attendance->check_in_lon,
                'time'=>$timestamp,
                'mileage'=>$attendance->check_in_mileage,
                'batteryLevel'=>$attendance->check_in_battery,
                'locationType'=>$attendance->check_in_loc_type
            ];
        }else {
            // If day ended
            $timestamp = time()*1000;
            $attendance_arr = [
                'type'=> 2,
                'lat'=>$attendance->check_out_lat,
                'lon'=>$attendance->check_out_lon,
                'time'=>$timestamp,
                'mileage'=>$attendance->check_out_mileage,
                'batteryLevel'=>$attendance->check_out_battery,
                'locationType'=>$attendance->check_out_loc_type
            ];
        }

        return response()->json([
            "result" => true,
            "attendance" => $attendance_arr
        ]);
    }
}