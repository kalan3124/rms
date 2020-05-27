<?php
namespace App\Http\Controllers\API\Medical\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ItineraryDate;
use App\Exceptions\WebAPIException;
use App\Models\BataType;

class BataTypeController extends Controller{
    public function getToday(){
        $user = Auth::user();

        $itineraryDate = ItineraryDate::getTodayForUser($user,['standardItineraryDate','standardItineraryDate.bataType','bataType','additionalRoutePlan','additionalRoutePlan.bataType'],null,true);

        if(!$itineraryDate)
            throw new WebAPIException("You haven't itinerary today.",12);

        if(isset($itineraryDate->bataType))
            $bataType = $itineraryDate->bataType;
        else if(isset($itineraryDate->standardItineraryDate)&&isset($itineraryDate->standardItineraryDate->bataType))
            $bataType = $itineraryDate->standardItineraryDate->bataType;
        else if(isset($itineraryDate->additionalRoutePlan)&&isset($itineraryDate->additionalRoutePlan->bataType))
            $bataType = $itineraryDate->additionalRoutePlan->bataType;
        else
            throw new WebAPIException("You have an error in your itinerary. Please recheck your today itinerary",31);

        return response()->json([
            "result"=>true,
            "bataType"=>[
                "id"=>$bataType->getKey(),
                "name"=>$bataType->bt_name,
            ]
        ]);

    }

    public function getAll(){
        $user = Auth::user();

        $type = 0;
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type')
        ])){
            $type=2;
        } else {
            $type=1;
        }

        $bataTypes = BataType::where('bt_type',$type)->where('divi_id',$user->divi_id)->get();
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
}