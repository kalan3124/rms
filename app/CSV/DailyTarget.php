<?php
namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\Area;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SfaDailyTarget;
use App\Models\User;

class DailyTarget extends Base{
    protected $title = "Daily Targets";

    protected $lastUser = 0;

     protected $columns = [
          'target_day'=>"Target Day (YYYY-mm-dd)",
          'day_target'=>"Day Target",
          'sr_code'=>"Sr Code",
          'ar_code'=>'Area Code'
     ];

     protected function formatValue($columnName, $value){
          switch ($columnName) {
               case 'sr_code':
                   if(!$value)
                       throw new WebAPIException("Please provide a user code!");
                   $user = User::where((new User)->getCodeName(),"LIKE",$value)->first();
                   if(!$user)
                       throw new WebAPIException("User not found! Given user code is '$value'");
                   return $user->getKey();
               case 'ar_code': 
                   if(!$value)
                       throw new WebAPIException("Please provide a Area code!");
                   $area = Area::where((new Area)->getCodeName(),"LIKE",$value)->first();
                   if(!$area)
                       throw new WebAPIException("Area not found! Given area code is '$value'");
                   return $area->getKey();
               default:
                   return ($value<=0||!$value)?null:$value;
          }
     }

     protected function insertRow($row){

          SfaDailyTarget::create([
               'target_day' => date('Y-m-d',strtotime($row['target_day'])),
               'day_target' => $row['day_target'],
               'sr_code' => $row['sr_code'],
               'ar_code' => $row['ar_code']
          ]);

          $user = User::where('id',$row['sr_code'])->first();

          if($user){
            $itineray = SalesItinerary::where('u_id',$user->id)
            ->where('s_i_year',date('Y',strtotime($row['target_day'])))
            ->where('s_i_month',date('m',strtotime($row['target_day'])))
            ->latest()
            ->first();

            if($itineray){
                $itinerayDate = SalesItineraryDate::where('s_i_id',$itineray['s_i_id'])->where('s_id_date',date('d',strtotime($row['target_day'])))->first();
                if($itinerayDate){
                    $itinerayDate->day_target = $row['day_target'];
                    $itinerayDate->save();
                }
                
            }
        }     
     }
}
?>