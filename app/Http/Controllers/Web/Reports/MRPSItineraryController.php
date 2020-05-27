<?php

namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use App\Form\Columns\ColumnController;
use App\Traits\Territory;
use App\Models\User;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\SubTown;
use App\Models\UserAttendance;
use App\Models\SpecialDay;
use Illuminate\Support\Facades\DB;
use App\Exceptions\WebAPIException;

class MRPSItineraryController extends ReportController{

    use Territory;

    protected $title = "MR & PS Itinerary";

    public function search(Request $request){

               $values = $request->input('values');

               if(!isset($values['u_id'])||!isset($values['u_id']['value']))
                    throw new WebAPIException("MR/PS field is required");

               $userId = User::find($values['u_id']['value']);
                // Seperate the year and month
               $year = date('Y',strtotime($values['month']));
               $month = date('m',strtotime($values['month']));  
               
               $formatedresulst = [];

               // Finding the itinerary
               $query = Itinerary::where([
                         'i_year' => $year,
                         'i_month' => $month,
                    ])
                    ->latest()
                    ->with([
                         'itineraryDates',
                         'itineraryDates.bataType',
                         'itineraryDates.joinFieldWorker',
                         'itineraryDates.additionalRoutePlan',
                         'itineraryDates.additionalRoutePlan.bataType',
                         'itineraryDates.itineraryDayTypes',
                         'itineraryDates.itineraryDayTypes.dayType',
                         'itineraryDates.standardItineraryDate',
                         'itineraryDates.standardItineraryDate.bataType',
                         'itineraryDates.changedItineraryDate',
                         'itineraryDates.changedItineraryDate.bataType',
                    ]);

                    $query->where('rep_id',$values['u_id']['value']);

             
                    $itinerary = $query->first();

                    $standardItineraryIds = $itinerary->itineraryDates->pluck('standardItineraryDate.sid_id')->all();
                    $additionalRoutePlanIds = $itinerary->itineraryDates->pluck('additionalRoutePlan.arp_id')->all();
                    $changedPlanIds = $itinerary->itineraryDates->pluck('changedItineraryDate.idc_id')->all();

                    $rows = $this->__getSubtownsByItineraryIds($standardItineraryIds,$additionalRoutePlanIds,$changedPlanIds,['uist.sid_id','uist.arp_id','uist.idc_id','st.sub_twn_id']);

                    $dates = $itinerary->itineraryDates->map(function($itineraryDate)use($rows){
                              $towns = collect([]);
                              $mileage = 0.00;
                              $bataType = null;
                  
                            if(isset($itineraryDate->changedItineraryDate)&&isset($itineraryDate->changedItineraryDate->idc_aprvd_at)){

                                $towns = $rows->where('idc_id',$itineraryDate->idc_id);
                                $mileage = $itineraryDate->changedItineraryDate->idc_mileage;
                                $bataType = isset($itineraryDate->changedItineraryDate->bataType)?$itineraryDate->changedItineraryDate->bataType:$bataType;
                
                            }else if(isset($itineraryDate->standardItineraryDate)){
                                  $towns = $rows->where('sid_id',$itineraryDate->sid_id);
                                  $mileage = $itineraryDate->standardItineraryDate->sid_mileage;
                                  $bataType = isset($itineraryDate->standardItineraryDate->bataType)?$itineraryDate->standardItineraryDate->bataType:$bataType;
                  
                              }else if(isset($itineraryDate->additionalRoutePlan)){
                                  $towns = $rows->where('arp_id',$itineraryDate->additionalRoutePlan->arp_id);
                                  $mileage = $itineraryDate->additionalRoutePlan->arp_mileage;
                                  $bataType = isset($itineraryDate->additionalRoutePlan->bataType)?$itineraryDate->additionalRoutePlan->bataType:$bataType;
                  
                              } else if(isset($itineraryDate->joinFieldWorker)){
                                    // Joint Field worker areas
                              } else {
                                  $mileage = $itineraryDate->id_mileage;
                                  $bataType = isset($itineraryDate->bataType)?$itineraryDate->bataType:$bataType;
                              }
                  
                              $dayTypes = $itineraryDate->itineraryDayTypes->filter(function($itinerarDayType){
                                  return !!$itinerarDayType->dayType;
                              });
                              
                              
                              $dayTypes = $dayTypes->map(function($itineraryDayType){
                                  return [
                                      "name"=>$itineraryDayType->dayType->dt_name,
                                      "id"=>$itineraryDayType->dayType->dt_id
                                  ];
                              });
                  
                              $bataType = $bataType?[
                                  "id"=>$bataType->getKey(),
                                  "name"=>$bataType->bt_name
                              ]:[
                                  "id"=>0,
                                  "name"=>""
                              ];
                  
                              $towns->transform(function($town){
                                  return [
                                      'name'=>$town->sub_twn_name??"NOT SET",
                                      'id'=>$town->sub_twn_id
                                  ];
                              });
                  
                              $towns = $towns->unique('id');
                  
                              return [
                                  "date"=>$itineraryDate->id_date,
                                  'types'=>$dayTypes->values()->toArray(),
                                  "towns"=>$towns->values()->toArray(),
                                  "mileage"=>$mileage,
                              ];
                              
                    });

                    $specialDays = SpecialDay::whereYear('sd_date',$year)->whereMonth('sd_date',$month)->get();

                    $specialDays->transform(function($specialDay){
                         return [
                              'date'=>date('d',strtotime($specialDay->sd_date)),
                              "towns"=>[],
                              "types"=>[],
                              "mileage"=>0.00,
                         ];
                    });

                    // $begin = new \DateTime(date('Y-m-01'));
                    // $end = new \DateTime(date('Y-m-t'));

                    // $interval = \DateInterval::createFromDateString('1 day');
                    // $period = new \DatePeriod($begin, $interval, $end);

                    $begin = new \DateTime(date('Y-m-01',strtotime($values['month'])));
                    $end = new \DateTime(date("Y-m-t",strtotime($values['month'])));
                    $end = $end->modify('1 day'); 

                    $interval = new \DateInterval('P1D');
                    $period = new \DatePeriod($begin, $interval ,$end);
               

                    $retArr = [];

                    $attendance = UserAttendance::whereBetween('check_in_time', [$begin, $end])
                         ->where('u_id',$values['u_id']['value'])
                         ->select([DB::raw('DATE(MIN(check_in_time)) AS check_in_time'),DB::raw('DATE(MAX(check_out_time)) AS check_out_time')])
                         ->groupBy(DB::raw('DATE(check_in_time)'))
                         ->get();

                         $attendance->transform(function($att){
                              return [
                                   'check_in_time' => $att->check_in_time?date('Y-m-d',strtotime($att->check_in_time)):NULL,
                                   'check_out_time' => $att->check_out_time?date('Y-m-d',strtotime($att->check_out_time)):NULL
                              ];
                         });

                         foreach ($period as $dt) {
                              $date = $dt->format("d");
                  
                              $itineraryDate = $dates->where('date',$date)->first();
                              $specialDay = $specialDays->where('date',$date)->first();
                              $attendanceForDay = $attendance->where('check_in_time',$dt->format('Y-m-d'))->first();
                  
                              $day =null;
                  
                              if($itineraryDate){
                                  $itineraryDate['date'] = $dt->format('Y-m-d');
                                  $day = $itineraryDate;
                              } else if($specialDay){
                                  $specialDay['date'] = $dt->format('Y-m-d');
                                  $day = $specialDay;
                              } else {
                                  $day = [
                                      'date'=>$dt->format('Y-m-d'),
                                      "towns"=>[],
                                      "types"=>[],
                                      "mileage"=>0.00,
                                  ];
                              }

                              $arr  = [
                                  [ 'name'=>'']
                              ];
                              $type = implode(",",array_map(function($value){
                                   return $value['name'];
                               },$day['types']));

                               $towns = implode("/",array_map(function($value){
                                   return $value['name'];
                               },$day['towns']));

                               if($towns){
                                   $print = $towns;
                               } else {
                                    $print = "No Towns for day";
                               }

                               if($type){
                                $days = $type;
                            } else {
                                 $days = "-";
                            }

                              $formatedresulst [] = [
                                   'date' => $day['date'],
                                   'dateType' => $days,
                                   'towns' => $print,
                                   'kms' => $day['mileage']
                              ];
                         }
          return[
               'count'=>0,
               'results'=>$formatedresulst
          ];
    }

    protected function setColumns(ColumnController $columnController, Request $request){
          $columnController->text('date')->setLabel("Date");
          $columnController->text('dateType')->setLabel("Plan Type");
          $columnController->text('towns')->setLabel("TownWork");
          $columnController->text('kms')->setLabel("Kms");
     }

     protected function setInputs($inputController){
          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setLink("team");
          $inputController->ajax_dropdown("divi_id")->setLabel("Division")->setLink("division");
          $inputController->ajax_dropdown('u_id')->setWhere(["divi_id" => "{divi_id}","tm_id" => "{team_id}",'u_tp_id'=> '3'.'|'.config('shl.product_specialist_type')])->setLabel('MR/PS or FM')->setLink('user');
          // $inputController->ajax_dropdown("u_id")->setLabel("User")->setLink("user");
          $inputController->date("month")->setLabel("month");
          $inputController->setStructure([
            ["team_id","u_id","divi_id","month"]
          ]);
     }
}

?>