<?php
namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\WebAPIException;
use App\Traits\Territory;
use App\Models\User;
use App\Models\Itinerary;
use App\Models\UserAttendance;
use App\Models\SpecialDay;
use Illuminate\Support\Facades\DB;

class MrPsItineraryReportController extends Controller{

     use Territory;

     public function __searchBy($values){

          // return $values['team']['value'];

               if(!isset($values['user'])||!isset($values['user']['value']))
                    throw new WebAPIException("MR/PS field is required!");

               $userId = User::find($values['user']['value']);
                // Seperate the year and month
               $year = date('Y',strtotime($values['e_date']));
               $month = date('m',strtotime($values['e_date']));  
               
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

                         $query->where('rep_id',$values['user']['value']);
             
                     $itinerary = $query->first();

                     if(!isset($itinerary))
                        throw new WebAPIException("Data Not found!");

                    $standardItineraryIds = $itinerary->itineraryDates->pluck('standardItineraryDate.sid_id')->all();
                    $additionalRoutePlanIds = $itinerary->itineraryDates->pluck('additionalRoutePlan.arp_id')->all();
                    $changedPlanIds = $itinerary->itineraryDates->pluck('changedItineraryDate.idc_id')->all();

                    $rows = $this->__getSubtownsByItineraryIds($standardItineraryIds,$additionalRoutePlanIds,$changedPlanIds,['uist.sid_id','uist.arp_id','st.sub_twn_id','uist.idc_id','st.sub_twn_id']);

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
                  
                            }else if(isset($itineraryDate->additionalRoutePlan)){
                                  $towns = $rows->where('arp_id',$itineraryDate->additionalRoutePlan->arp_id);
                                  $mileage = $itineraryDate->additionalRoutePlan->arp_mileage;
                  
                            } else if(isset($itineraryDate->joinFieldWorker)){
                
                            } else {
                                $mileage = $itineraryDate->id_mileage;
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

                    $begin = new \DateTime(date('Y-m-01',strtotime($values['e_date'])));
                    $end = new \DateTime(date("Y-m-t",strtotime($values['e_date'])));
                    $end = $end->modify('1 day'); 

                    $interval = new \DateInterval('P1D');
                    $period = new \DatePeriod($begin, $interval ,$end);
               

                    $retArr = [];

                    $attendance = UserAttendance::whereBetween('check_in_time', [$begin, $end])
                         ->where('u_id',$values['user']['value'])
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
                                    $day['date'],
                                    $days,
                                    $print,
                                    $day['mileage']
                              ];
                         }
          return[
               'count'=>0,
               'results'=>$formatedresulst
          ];
     }

     public function search(Request $request){
          $values = $request->input('values',[]);
  
          return $this->__searchBy($values);
     }
}
?>