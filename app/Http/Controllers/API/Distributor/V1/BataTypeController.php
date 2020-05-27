<?php

namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\SalesAPIException;
use App\Models\BataType;
use App\Exceptions\DisAPIException;
use App\Models\SfaExpenses;

class BataTypeController extends Controller{

     public function getBataTypes(){
     $user = Auth::user();

     $bataTypes = BataType::where('bt_type',3)->get();
        $bataTypes->transform(function($bataType){
            return [
                'id'=>$bataType->getKey(),
                'name'=>$bataType->bt_name
            ];
        });

        return response()->json([
            'result'=>true,
            'bataTypes'=>$bataTypes
        ]);
     }

     public function saveBataExpenses(Request $request){

          $user= Auth::user();
          // Checking the request is empty
          if(!$request->has('jsonString'))
               throw new DisAPIException('Some parameters are not found', 5);

          // Decoding the json
          $json_decode = json_decode($request['jsonString'],true);
          $json_app_version = json_decode($request['app_version'],true);

          // Java Timestamp = PHP Unix Timestamp * 1000
          $timestamp = $json_decode['expTime'] / 1000;

          if(!isset($json_decode['expTime'])){
               $timestamp = time();
          }

          // Formating the unix timestamp to a string
          $expense_time = date("Y-m-d h:i:s", $timestamp);
          
          $validator = Validator::make($json_decode, [
               'bt_id' => 'required',
               'stationery' => 'required',
               'parking' => 'required',
               'remark' => 'required'
          ]);

          $appVersion = null;
          if(isset($request['appVersion'])){
               $appVersion = $request['appVersion'];
          }

          // Throw an exception if required parameters not supplied
          if ($validator->fails()) 
               throw new DisAPIException($validator->errors()->first(), 4);

          
               $expenses = SfaExpenses::create([
                    'bt_id'=> $json_decode['bt_id'],
                    'stationery'=> $json_decode['stationery'],
                    'parking'=> $json_decode['parking'],
                    'remark'=> $json_decode['remark'],
                    'app_version'=> $appVersion,
                    'exp_time'=> $expense_time,
                    'u_id'=> $user->getKey(),
                    'mileage'=>$json_decode['mileage']
               ]);

          return response()->json([
               'result'=>true,
               'message'=>"Expenses Successfully Saved!!!"
          ]);
     }
}
?>