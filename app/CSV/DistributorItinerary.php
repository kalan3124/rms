<?php
namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\BataType;
use App\Models\DayType;
use App\Models\Route;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SalesItineraryDateDayType;
use App\Models\SalesItineraryDateJFW;
use App\Models\User;

class DistributorItinerary extends Base {
    protected $title = "Distributor Itinerary";

    protected $lastUser = 0;

    protected $lastMonth = '0000-00';

    protected $columns = [
        'u_id'=>"DSR/SR Code",
        'date'=>'Date [YYYY-MM-DD]',
        'day_types'=>'Day Type Codes (Comma separated)',
        'mileage'=>"Mileage",
        'bt_id'=>'Bata Type Code',
        'route_id'=>'Route Code',
        'jfw_id'=>'Joint Field Worker',
    ];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'u_id':
                $user = User::where((new User)->getCodeName(),$value)->first();

                if(!$user)
                    throw new WebAPIException("User not found for '{$value}'.");
                return $user->getKey();
            case 'date':
                $date = strtotime($value);

                if(!$date)
                    throw new WebAPIException("Incorrect date supplied. '$value'");

                return $date;
            case 'bt_id':
                $bataType = BataType::where('bt_type',6)->where((new BataType)->getCodeName(),$value)->first();

                if(!$bataType)
                    return null;

                return $bataType->getKey();
            case 'route_id':
                $route = Route::where('route_type',1)->where('route_code',$value)->first();

                if(!$route)
                    return null;
                return $route->getKey();

            case 'jfw_id':
                $jfw = User::where((new User)->getCodeName(),$value)->first();

                if(!$jfw)
                    return null;

                return $jfw->getKey();

            case 'day_types':
                $values = explode(',',$value);
                $ids = [];

                foreach ($values as $key => $value) {
                    $dayType = DayType::where('dt_code', trim( $value))->first();

                    if($dayType)
                        $ids[] = $dayType->getKey();
                }

                return $ids;
            default:
                return $value;
        }
    }

    protected function insertRow($row)
    {
        if($this->lastUser!=$row['u_id']&&$this->lastMonth!=date('Y-m',$row['date'])){
            $itinerary = SalesItinerary::create([
                'u_id'=>$row['u_id'],
                's_i_year'=>date('Y',$row['date']),
                's_i_month'=>date('m',$row['date'])
            ]);
        } else {
            $itinerary = SalesItinerary::where('u_id',$row['u_id'])
                ->where('s_i_year',date('Y',$row['date']))
                ->where('s_i_month',date('m',$row['date']))
                ->latest()
                ->first();
        }

        /** @var SalesItinerary $itinerary */

        $itineraryDate = SalesItineraryDate::create([
            's_id_date'=>date('d',$row['date']),
            's_id_mileage'=>$row['mileage'],
            's_i_id'=>$itinerary->s_i_id,
            'route_id'=>$row['route_id'],
            'bt_id'=>$row['bt_id'],
            's_id_type'=>0
        ]);

        if(isset($row['jfw_id'])){
            SalesItineraryDateJFW::create([
                's_id_id'=>$itineraryDate->getKey(),
                'u_id'=>$row['jfw_id']
            ]);
        }

        foreach ($row['day_types'] as $key => $dayType) {
            SalesItineraryDateDayType::create([
                's_id_id'=>$itineraryDate->getKey(),
                'dt_id'=>$dayType
            ]);
        }
    }
}