<?php

namespace App\Http\Controllers\Web\Sales;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\DayType;
use App\Models\DCSalesItinerary;
use App\Models\DCSalesItineraryDate;
use App\Models\DCSalesItineraryDateDayType;
use App\Models\DCSalesItineraryDateJFW;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DCItineraryController extends Controller {
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

        // return $request->all();die;

        // return 'aa';

       if($validation->fails()){
           throw new WebAPIException($validation->errors()->first());
       }

       $year  = $request->input('year');
       $month = $request->input('month');
       $users = $request->input('user');
       $dates = $request->input('dates');

       foreach ($users as $key => $user) {

            $salesItinerary = DCSalesItinerary::create([
                'sdc_i_year'=>$year,
                'sdc_i_month'=>$month,
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

                $salesItineraryDate = DCSalesItineraryDate::create([
                    'sdc_id_date'=>$date,
                    'sdc_id_mileage'=>$mileage,
                    'bt_id'=>$bataType,
                    'route_id'=>$route,
                    'sdc_id_type'=>0,
                    'sdc_i_id'=>$salesItinerary->getKey(),
                    'day_target' =>$day_target
                ]);

                foreach ($dayTypes as $key => $dayTypeId) {
                    DCSalesItineraryDateDayType::create([
                        'dt_id'=>$dayTypeId,
                        'sdc_id_id'=>$salesItineraryDate->getKey()
                    ]);
                }

                foreach ($jointFieldWorkers as $key => $jointFieldWorker) {
                    DCSalesItineraryDateJFW::create([
                        'sdc_id_id'=>$salesItineraryDate->getKey(),
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

        // return $request->all();
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

        // return $request->all();
        $dcSalesItinerary = DCSalesItinerary::where([
            'sdc_i_year'=>$year,
            'sdc_i_month'=>$month,
            'u_id'=>$userId
        ])->with([
            'dcSalesItineraryDates',
            'dcSalesItineraryDates.jointFieldWorkers',
            'dcSalesItineraryDates.jointFieldWorkers.user',
            'dcSalesItineraryDates.dcSalesItineraryDateDayTypes',
        ])->latest()->first();


        // return $dcSalesItinerary;
        $itineraryDates = [];

        if($dcSalesItinerary){

            $itineraryDates = $dcSalesItinerary->dcSalesItineraryDates->map(function(DCSalesItineraryDate $dcSalesItinerarDate){
                $bataType = null;
                $dayTypes = [];
                $route = null;

                if($dcSalesItinerarDate->bataType){
                    $bataType = [
                        'value'=>$dcSalesItinerarDate->bataType->getKey(),
                        'label'=>$dcSalesItinerarDate->bataType->bt_name
                    ];
                }

                if($dcSalesItinerarDate->route){
                    $route = [
                        'value'=>$dcSalesItinerarDate->route->getKey(),
                        'label'=>$dcSalesItinerarDate->route->route_name
                    ];
                }

                $dcSalesItinerarDate->jointFieldWorkers->transform(function( DCSalesItineraryDateJFW $salesItineraryDateJFW ){

                    if(!$salesItineraryDateJFW->user){
                        return null;
                    }

                    return [
                        'value'=>$salesItineraryDateJFW->user->getKey(),
                        'label'=>$salesItineraryDateJFW->user->name
                    ];

                });

                $jointFieldWorkers = $dcSalesItinerarDate->jointFieldWorkers->filter(function($jointFieldWorker){return !!$jointFieldWorker;})->values();

                $dayTypes = $dcSalesItinerarDate->dcSalesItineraryDateDayTypes->map(function(DCSalesItineraryDateDayType $dcSalesDayType){
                    if(!$dcSalesDayType->dayType)
                        return null;

                    return $dcSalesDayType->dayType->getKey();
                })->filter(function($id){return !!$id;})->values();

                return [
                    'date'=>$dcSalesItinerarDate->sdc_id_date,
                    'mileage'=>$dcSalesItinerarDate->sdc_id_mileage,
                    'route'=>$route,
                    'bataType'=>$bataType,
                    'dayTypes'=>$dayTypes,
                    'mode'=>$dcSalesItinerarDate->sdc_id_type,
                    'jointFieldWorkers'=>$jointFieldWorkers->count()?$jointFieldWorkers:null,
                    'day_tar'=>$dcSalesItinerarDate->day_target?$dcSalesItinerarDate->day_target:null
                ];
            });
        }

        // return
        $dayTypes = DayType::get();
        // return $dayTypes;


        $dayTypes->transform(function(DayType $dayType){
            return [
                'label'=>$dayType->dt_code,
                'value'=>$dayType->getKey(),
                'color'=>config('shl.color_codes')[$dayType->dt_color],
                'isFieldWorking'=>(int) $dayType->dt_field_work_day,
                'isWorking'=>(int)$dayType->dt_is_working
            ];
        });
        // return $dayTypes;



        return response()->json([
            'success'=>true,
            'dates'=>$itineraryDates,
            'dayTypes'=>$dayTypes,
            'enabledModes'=>[1]
        ]);

    }
}
