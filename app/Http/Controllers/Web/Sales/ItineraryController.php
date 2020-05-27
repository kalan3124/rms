<?php

namespace App\Http\Controllers\Web\Sales;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\DayType;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SalesItineraryDateDayType;
use App\Models\SalesItineraryDateJFW;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItineraryController extends Controller {
    
    public function save(Request $request){
        // return $request->all();die;
       $validation = Validator::make($request->all(),[
            'year'=>'required',
            'month'=>'required',
            'user'=>'required',
            'dates.*'=>'required|array',
            'dates.*.date'=>'required|numeric',
            'dates.*.dayTypes'=>'required|array'
       ]);

       if($validation->fails()){
           throw new WebAPIException($validation->errors()->first());
       }

       $year = $request->input('year');
       $month = $request->input('month');
       $users = $request->input('user');
       $dates = $request->input('dates');

       foreach ($users as $key => $user) {
           
            $salesItinerary = SalesItinerary::create([
                's_i_year'=>$year,
                's_i_month'=>$month,
                'u_id'=>$user['value']
            ]);

            foreach ($dates as $key => $dateDetails) {

                $date = isset($dateDetails['date'])?$dateDetails['date']:0.00;
                $mileage = isset($dateDetails['mileage'])?$dateDetails['mileage']:0.00;
                $route = isset($dateDetails['route'])?$dateDetails['route']['value']:null;
                $bataType = isset($dateDetails['bataType'])?$dateDetails['bataType']['value']:null;
                $dayTypes = isset($dateDetails['dayTypes'])?$dateDetails['dayTypes']:[];
                $jointFieldWorkers = isset($dateDetails['jointFieldWorkers'])?$dateDetails['jointFieldWorkers']:[];
                $day_target = isset($dateDetails['day_tar'])?$dateDetails['day_tar']:null;

                $salesItineraryDate = SalesItineraryDate::create([
                    's_id_date'=>$date,
                    's_id_mileage'=>$mileage,
                    'bt_id'=>$bataType,
                    'route_id'=>$route,
                    's_id_type'=>0,
                    's_i_id'=>$salesItinerary->getKey(),
                    'day_target' =>$day_target
                ]);
                
                foreach ($dayTypes as $key => $dayTypeId) {
                    SalesItineraryDateDayType::create([
                        'dt_id'=>$dayTypeId,
                        's_id_id'=>$salesItineraryDate->getKey()
                    ]);
                }

                foreach ($jointFieldWorkers as $key => $jointFieldWorker) {
                    SalesItineraryDateJFW::create([
                        's_id_id'=>$salesItineraryDate->getKey(),
                        'u_id'=>$jointFieldWorker['value']
                    ]);
                }
            }
        }

        return response()->json([
            'success'=>true,
            'message'=>"Successfully added the itinerary."
        ]);
    }

    public function load(Request $request){
        $validation = Validator::make($request->all(),[
            'year'=>'required',
            'month'=>'required',
            'user'=>'required|array',
            'user.value'=>'required|numeric'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request.");
        }

        $year = $request->input('year');
        $month = $request->input('month');
        $userId = $request->input('user.value');


        $salesItinerary = SalesItinerary::where([
            's_i_year'=>$year,
            's_i_month'=>$month,
            'u_id'=>$userId
        ])->with([
            'salesItineraryDates',
            'salesItineraryDates.jointFieldWorkers',
            'salesItineraryDates.jointFieldWorkers.user',
            'salesItineraryDates.salesItineraryDateDayTypes',
        ])->latest()->first();

        $itineraryDates = [];

        if($salesItinerary){

            $itineraryDates = $salesItinerary->salesItineraryDates->map(function(SalesItineraryDate $salesItinerarDate){
                $bataType = null;
                $dayTypes = [];
                $route = null;

                if($salesItinerarDate->bataType){
                    $bataType = [
                        'value'=>$salesItinerarDate->bataType->getKey(),
                        'label'=>$salesItinerarDate->bataType->bt_name
                    ];
                }

                if($salesItinerarDate->route){
                    $route = [
                        'value'=>$salesItinerarDate->route->getKey(),
                        'label'=>$salesItinerarDate->route->route_name
                    ];
                }

                $salesItinerarDate->jointFieldWorkers->transform(function( SalesItineraryDateJFW $salesItineraryDateJFW ){

                    if(!$salesItineraryDateJFW->user){
                        return null;
                    }

                    return [
                        'value'=>$salesItineraryDateJFW->user->getKey(),
                        'label'=>$salesItineraryDateJFW->user->name
                    ];
                    
                });

                $jointFieldWorkers = $salesItinerarDate->jointFieldWorkers->filter(function($jointFieldWorker){return !!$jointFieldWorker;})->values();
                
                $dayTypes = $salesItinerarDate->salesItineraryDateDayTypes->map(function(SalesItineraryDateDayType $salesDayType){
                    if(!$salesDayType->dayType)
                        return null;

                    return $salesDayType->dayType->getKey();
                })->filter(function($id){return !!$id;})->values();

                return [
                    'date'=>$salesItinerarDate->s_id_date,
                    'mileage'=>$salesItinerarDate->s_id_mileage,
                    'route'=>$route,
                    'bataType'=>$bataType,
                    'dayTypes'=>$dayTypes,
                    'mode'=>$salesItinerarDate->s_id_type,
                    'jointFieldWorkers'=>$jointFieldWorkers->count()?$jointFieldWorkers:null,
                    'day_tar'=>$salesItinerarDate->day_target?$salesItinerarDate->day_target:null
                ];
            });
        }

        $dayTypes = DayType::get();

        $dayTypes->transform(function(DayType $dayType){
            return [
                'label'=>$dayType->dt_code,
                'value'=>$dayType->getKey(),
                'color'=>config('shl.color_codes')[$dayType->dt_color],
                'isFieldWorking'=>(int) $dayType->dt_field_work_day,
                'isWorking'=>(int)$dayType->dt_is_working
            ];
        });

        return response()->json([
            'success'=>true,
            'dates'=>$itineraryDates,
            'dayTypes'=>$dayTypes,
            'enabledModes'=>[1]
        ]);

    }
}