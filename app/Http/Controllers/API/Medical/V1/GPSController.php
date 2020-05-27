<?php

namespace App\Http\Controllers\API\Medical\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;
use App\Exceptions\MediAPIException;
use App\Models\GPSStatusChange;
use App\Models\GPSTracking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class GPSController extends Controller{
    public function save(Request $request){
        $validator = Validator($request->all(),[
            'location'=>"required|json"
        ]);

        $user = Auth::user();

        if($validator->fails()){
            $errors = $validator->errors();
            throw new MediAPIException("Validation failed. ".$errors->first(),17);
        }

        $locationString = $request->input('location');

        // if(\strlen($locationString)>546440*2){
        //     return response()->json([
        //         'result'=>true,
        //         "message"=>"GPS Data ignored."
        //     ]);
        // }

        $locationArray = json_decode($locationString,true);

        // $arrayValidator = Validator::make(['locations'=>$locationArray],[
        //     "locations"=>"required|array",
        //     "locations.*.provider"=>[
        //         'required',Rule::in(['network','gps','undefined'])
        //     ],
        //     'locations.*.time'=>'required|date_format:H:i:s',
        //     'locations.*.date'=>'required|date',
        //     'locations.*.speed'=>'required|numeric',
        //     'locations.*.accuracy'=>'required|numeric',
        //     'locations.*.bearing'=>'required|numeric',
        //     'locations.*.bat_level'=>'required|numeric',
        //     'locations.*.lon'=>'required|numeric',
        //     'locations.*.lat'=>'required|numeric',
        // ]);

        // if($arrayValidator->fails()){
        //     throw new MediAPIException("Validation failed. ".$arrayValidator->errors()->first(),18);
        // }

        $locations = collect($locationArray);

        // if($locations->count()>5000){
        //     return response()->json([
        //         'result'=>true,
        //         "message"=>"GPS Data ignored."
        //     ]);
        // }

        $locations->sortBy('time');
        DB::beginTransaction();
        try{
            $count = 0;

            $modedLocations = [];

            foreach($locations as $key =>$location ){
                $insert = true;

                if($key!=0){
                    $previousLocation = $locations[$key-1];

                    if($previousLocation['lat']==$location['lat']&&$previousLocation['lon']==$location['lon']){
                        $insert=false;
                    }
                }

                if($insert){
                    $modedLocations[] = [
                        "u_id"=>$user->getKey(),
                        "gt_lon"=>$location["lon"],
                        "gt_lat"=>$location["lat"],
                        "gt_btry"=>$location["bat_level"],
                        "gt_speed"=>$location["speed"],
                        "gt_time"=>$location['date'].' '.$location["time"],
                        "gt_brng"=>$location["bearing"],
                        "gt_accu"=>$location["accuracy"],
                        "gt_prvdr"=>$location["provider"]=="network"?1:($location["provider"]=="gps"?0:2)
                    ];
                    $count++;
                }
            }

            GPSTracking::insert($modedLocations);

            DB::commit();

            return [
                'result'=>true,
                "message" => "GPS Syncronized successfully. $count coordinates inserted.".($count!==$key+1?" And ".($key+1-$count)." coordinates skipped inserting due to duplications.":"")
            ];
        } catch(\Exception $e){
            DB::rollBack();
            throw new MediAPIException("GPS coordinates not synced.",19);
        }
    }

    public function statusChange(Request $request){
        $validator = Validator($request->all(),[
            'location'=>"required|json"
        ]);

        $user = Auth::user();

        if($validator->fails()){
            $errors = $validator->errors();
            throw new MediAPIException("Validation failed. ".$errors->first(),17);
        }

        $locationString = $request->input('location');

        $locationArray = json_decode($locationString,true);

        $locations = collect($locationArray);

        $locations->sortBy('time');
        DB::beginTransaction();
        try{
            $count = 0;

            $modedLocations = [];

            foreach($locations as $key =>$location ){

                if($key>0){
                    $previousStatus = $locations[$key-1]['is_enable']==1?0:1;
                } else {
                    $previousCoordinate = GPSStatusChange::where('u_id',$user->getKey())->latest()->first();

                    if($previousCoordinate)
                        $previousStatus = $previousCoordinate->gsc_status;
                    else
                        $previousStatus = $locations[$key]['is_enable'];
                }

                if(($previousStatus==1&&$location['is_enable']==1)||($previousStatus==0&&$location['is_enable']==0)){
                    $modedLocations[] = [
                        "u_id"=>$user->getKey(),
                        "gsc_lon"=>$location["lon"],
                        "gsc_lat"=>$location["lat"],
                        "gsc_btry"=>$location["bat_level"],
                        "gsc_speed"=>$location["speed"],
                        "gsc_time"=>$location['date'].' '.$location["time"],
                        "gsc_brng"=>$location["bearing"],
                        "gsc_accu"=>$location["accuracy"],
                        "gsc_prvdr"=>$location["provider"]=="network"?1:($location["provider"]=="gps"?0:2),
                        'gsc_status'=>$location['is_enable']==1?0:1
                    ];
                    $count++;
                }
            }

            GPSStatusChange::insert($modedLocations);

            DB::commit();

            return [
                'result'=>true,
                "message" => "GPS Syncronized successfully. $count coordinates inserted.".($count!==$key+1?" And ".($key+1-$count)." coordinates skipped inserting due to duplications.":"")
            ];
        } catch(\Exception $e){
            DB::rollBack();
            throw new MediAPIException("GPS coordinates not synced.",19);
        }
    }
}