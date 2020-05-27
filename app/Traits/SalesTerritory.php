<?php 
namespace App\Traits;

use App\Models\User;
use App\Models\SalesItineraryDate;
use App\Models\Route;
use App\Models\SalesItinerary;

trait SalesTerritory
{
    /**
     * Returning the today allocated areas by itinerary for a user
     *
     * You can pluck sub town ids, area ids ,... from this collection
     *
     * @param \App\Models\User $user
     * @param int $day unix timestamp of the day
     * @param bool $approved only take approved 
     * @return \Illuminate\Support\Collection
     */
    public function getRoutesByItinerary($user,$day=null,$approved=true)
    {
        $itineraryDate = SalesItineraryDate::getTodayForUser($user,[],$day,false,$approved);

        $routeId = 0;
        // Retrieving route details related to above itienerary date
        if(isset($itineraryDate->route_id)){
            $routeId = $itineraryDate->route_id;  
        }

        $itineraryRoute = $this->__getRouteNameById($routeId);

        return $itineraryRoute;
    }

    protected function __getRouteNameById($route){

        $routeName = Route::where('route_id',$route)->get();

        $routeName->transform(function($route){
            return [
                'route_id'=> $route->route_id,
                'route_code' => $route->route_code,
                'route_name' => $route->route_name
            ];
        });

        return $routeName;
    }

}