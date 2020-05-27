<?php 
namespace App\Form;

use App\Models\DayTarget as AppDayTarget;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\User;

class DayTarget extends Form{
     protected $title = 'Day Target';

    protected $dropdownDesplayPattern = 'day_target';

     public function setInputs(\App\Form\Inputs\InputController $inputController)
     {
          $inputController->text('target_day')->setLabel('Target Day');
          $inputController->text('day_target')->setLabel('Day Target');
          $inputController->text('sr_code')->setLabel('Sr Code')->isUpperCase();
          $inputController->text('ar_code')->setLabel('Area Code');

          $inputController->setStructure([
               'target_day',
               'day_target',
               'sr_code',
               'ar_code'
          ]);
     }

     public function filterDropdownSearch($query,$where){
              if(isset($where['u_id']) && isset($where['date'])){
                   $user = User::where('id',$where['u_id']['value'])->first();

                    $query->where('sr_code',$user->u_code);
                    $query->where('target_day',$where['date']);
                    
              }

              unset($where['user']);
     }
}
?>