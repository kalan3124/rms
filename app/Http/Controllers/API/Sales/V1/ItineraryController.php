<?php
namespace App\Http\Controllers\API\Sales\V1;

use App\Exceptions\SalesAPIException;
use App\Http\Controllers\Controller;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItineraryController extends Controller {
    public function monthlyItinerary (Request $request){
        $year = date('Y');
        $month = date('m');
        $user = Auth::user();

        $salesItinerary = SalesItinerary::where('s_i_year',$year)
            ->where('s_i_month',$month)
            ->where('u_id',$user->getKey())
            ->whereNotNull('s_i_aprvd_at')
            ->with(['salesItineraryDates','salesItineraryDates.route','salesItineraryDates.route','salesItineraryDates.salesItineraryDateDayTypes','salesItineraryDates.salesItineraryDateDayTypes.dayType'])
            ->latest()
            ->first();

        if(!$salesItinerary){
            throw new SalesAPIException("Can not find an itinerary for you or your JFW.",11);
        }
        
        $salesItinerary->salesItineraryDates->transform(function(SalesItineraryDate $salesItineraryDate){

            return [
                'mileage'=>$salesItineraryDate->s_id_mileage,
                'bata_type_name'=>$salesItineraryDate->bataType?$salesItineraryDate->bataType->bt_name:null,
                'route_name'=>$salesItineraryDate->route?$salesItineraryDate->route->route_name:"Not Assigned",
                'day'=>$salesItineraryDate->s_id_date,
                'day_status'=>$salesItineraryDate->route?1:0,
                'working_status'=>$salesItineraryDate->route?1:0
            ];
        });

        return response()->json([
            'result'=>true,
            'itinerary_data'=>$salesItinerary->salesItineraryDates,
            'aproved'=>!!$salesItinerary->s_i_aprvd_at
        ]);
    }
}