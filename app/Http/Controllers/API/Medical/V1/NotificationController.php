<?php

namespace App\Http\Controllers\API\Medical\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\MediAPIException;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use function GuzzleHttp\json_decode;
use App\Models\User;
use App\Models\ItineraryDateChange;

class NotificationController extends Controller{
    public function sync(Request $request){
        $validation = Validator::make($request->all(),[
            'seenIds'=>'required',
            'timestamp'=>'required'
        ]);

        if($validation->fails()){
            throw new MediAPIException("Some parameters not supplied",5);
        }

        $seenIds = $request->input('seenIds',"[]");
        $seenIds = json_decode($seenIds,true);
        $user = Auth::user();

        $timestamp = $request->input('timestamp',0);

        if(count($seenIds)>0){
            Notification::whereIn('n_id',$seenIds)->where('u_id',$user->getKey())->update(['n_seen'=>1]);
        }


        if($timestamp==0){
            $notifications = Notification::where('u_id',$user->getKey())->latest()->take(30)->get();
        } else {
            $timestamp = date('Y-m-d H:i:s',$timestamp/1000);

            $notifications = Notification::where('created_at','>',$timestamp)->where('u_id',$user->getKey())->latest()->get();
        }

        $users = User::getByUser($user);

        $notifications->transform(function($notification)use($users){

            $additional = new \stdClass;

            if($notification->n_type==1){

                $itineraryDateChanged = ItineraryDateChange::whereIn('u_id',$users->pluck('id')->all())
                    ->whereDate('idc_date',$notification->created_at->format('Y-m-d'))
                    ->whereNull('idc_aprvd_u_id')
                    ->with(['user','bataType','areas','areas.subTown'])
                    ->latest()
                    ->first();
                
                if($itineraryDateChanged){
                    $additional = [];
                    $additional['date'] = $itineraryDateChanged->idc_date;
                    $additional['id']= $itineraryDateChanged->getKey();
                    $additional['user'] = $itineraryDateChanged->user?[
                        "name"=>$itineraryDateChanged->user->name,
                        'id'=>$itineraryDateChanged->user->getKey()
                    ]:[
                        'name'=>"DELETED",
                        "id"=>0
                    ];
                    $additional['bataType'] = $itineraryDateChanged->bataType?[
                        "name"=>$itineraryDateChanged->bataType->bt_name,
                        'id'=>$itineraryDateChanged->bataType->getKey()
                    ]:[
                        'name'=>"DELETED",
                        "id"=>0
                    ];

                    $towns = $itineraryDateChanged->areas->map(function($area){
                        if($area->subTown){
                            return [
                                'name'=>$area->subTown->sub_twn_name,
                                'id'=>$area->subTown->getKey()
                            ];
                        } else {
                            return [
                                'name'=>"DELETED",
                                'id'=>0
                            ];
                        }
                    });

                    $additional['towns']=$towns;
                    $additional['mileage'] = $itineraryDateChanged->idc_mileage;
                }
            }
            
            return [
                'id'=>$notification->n_id,
                'title'=>$notification->n_title,
                'content'=>$notification->n_content,
                'seen'=>$notification->n_seen,
                'time'=>$notification->created_at->timestamp*1000,
                'type'=>$notification->n_type,
                'additional'=>$additional
            ];
        });

        return response()->json([
            'result'=>!$notifications->isEmpty(),
            'notifications'=>$notifications
        ]);
    }
}