<?php
namespace App\Http\Controllers\Web\Reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Form\Columns\ColumnController;
use App\Models\Team;
use App\Models\StandardItinerary;
use App\Models\StandardItineraryDate;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\UserAttendance;
use \DateTime;
use DateInterval;
use DatePeriod;
use App\Models\VehicleTypeRate;
use App\Models\Expenses;
use App\Exceptions\WebAPIException;
use App\Form\User;
use App\Models\User as AppUser;
use App\Models\StationMileage;

class MilageReportController extends ReportController{

     protected $title = "Mileage Report";

     public function search(Request $request){
          $values = $request->input('values');

          $query = DB::table('teams as t')
               ->select('t.tm_name','t.tm_id','tu.u_id','u.id','u.name','u.divi_id')
               ->join('team_users as tu','tu.tm_id','t.tm_id')
               ->join('users as u','tu.u_id','u.id')
               ->whereNull('tu.deleted_at')
               ->whereNull('u.deleted_at')
               ->whereNull('t.deleted_at');
               // ->groupBy('u.id');

          if($request->has('values.user.value')){
               $query->where('u.id',$request->input('values.user.value'));
          }

          if($request->has('values.team_id.value')){
               $query->where('t.tm_id',$request->input('values.team_id.value'));
          }

          if($request->has('values.divi_id.value')){
               $query->where('u.divi_id',$request->input('values.divi_id.value'));
          }

          $count_new = $this->paginateAndCount($query,$request,'tm_id');
          $results = $query->get();

          // return $results;
          $formatedresulst = [];
          $total_mileage = 0;
          $total_mileage_allow = 0;
          $total_primileage = 0;
          $total_primileage_allow = 0;

          $total_e_mileage = 0;
          $total_e_mileage_allow = 0;
          $total_e_primileage = 0;
          $total_e_primileage_allow = 0;

          $tot_mileage_km = 0;

          $results->transform(function($data){
               $data->team_name = $data->tm_id;
               return $data;
          });
          // return $results->where('tm_id',108)->count();

          foreach ($results as $key => $value) {
               $count = $results->where('team_name',$value->tm_id)->count();
               if($key){
                    $tm_id = $value->tm_id;
                    $prevRow = $results[$key-1];
                    $prevTm_id = $prevRow->tm_id;

                    if($tm_id != $prevTm_id){
                         $rowNew['team_name'] = $value->tm_name;
                         $rowNew['team_name_rowspan'] = $count;
                    } else {
                         $rowNew['team_name'] = null;
                         $rowNew['team_name_rowspan'] = 0;
                    }

               } else {
                    $rowNew['team_name'] = $value->tm_name;
                    $rowNew['team_name_rowspan'] = $count;
               }

               $year = date('Y',strtotime($values['s_date']));
               $month = date('m',strtotime($values['s_date']));

               $itinerary = Itinerary::where('rep_id',$value->id)
               //  ->whereNotNull('i_aprvd_at')
                ->where('i_year',$year)
                ->where('i_month',$month)
                ->latest()
                ->first();

               $itineraryRelations = [
                    'standardItineraryDate',
                    'additionalRoutePlan',
                    'changedItineraryDate',
                    'itinerary'
               ];

               $itineraryDates = ItineraryDate::with($itineraryRelations)
               ->where('i_id',$itinerary['i_id'])
               ->get();

               $start    = (new DateTime(date('Y-m-01',strtotime($values['s_date']))));
               $end      = (new DateTime(date('Y-m-t',strtotime($values['s_date']))));
               $interval = DateInterval::createFromDateString('1 month');
               $period   = new DatePeriod($start, $interval, $end);

               $itineraryDates->transform(function( ItineraryDate $itineraryDate)use($itinerary,$value,$period){
                    $e_mileage = 0;
                    $user_details = AppUser::where('id',$value->id)->first();
                    $formatedDate = $itineraryDate->getFormatedDetails();
                    $mileage= $formatedDate->getMileage();

                    $itineraryss = $itinerary->where('i_id',$itineraryDate->i_id)->first();
                    $date = $itineraryss->i_year."-".str_pad($itineraryss->i_month,2,"0",STR_PAD_LEFT).'-'.str_pad($itineraryDate->id_date,2,"0",STR_PAD_LEFT);

                    $vehicleTypeRateInst = VehicleTypeRate::where('vht_id',$user_details->vht_id)->whereDate('vhtr_srt_date','<=',$date)->where('u_tp_id',$user_details->u_tp_id)->latest()->first();
                    $vehicleTypeRate = $vehicleTypeRateInst?$vehicleTypeRateInst->vhtr_rate:0;

                    $attendanceStatus = UserAttendance::where('u_id','=',$value->id)
                                        ->whereDate('check_in_time','=',$date)
                                        ->whereNotNull('check_out_time')
                                        ->latest()
                                        ->first();
                    $backDatedExpences = Expenses::where('u_id',$value->id)->whereDate('exp_date',$date)->first();

                    $stationMileage = StationMileage::where('u_id',$value->id)->where('exp_date',$date)->first();

                    if($attendanceStatus || $backDatedExpences || $stationMileage){
                         $e_mileage = $mileage;
                    } else {
                         $e_mileage = 0;
                    }

                    return [
                         "mileage"=>$mileage,
                         'vehicleTypeRate' =>$vehicleTypeRate,
                         'mileage_value' => $mileage*$vehicleTypeRate,
                         'e_mileage' => $e_mileage
                    ];
               });

               if(date('m',strtotime($values['s_date'])) != date('m')){
                    $table_name = 'gps_tracking_'.date('Y',strtotime($values['s_date'])).'_'.date('m',strtotime($values['s_date']));
               } else {
                    $table_name = 'gps_tracking';
               }

               $station_sum = 0;
               // foreach($period as $date){
                    $query = UserAttendance::whereDate('check_in_time','>=',date('Y-m-01',strtotime($values['s_date'])))
                                             ->whereDate('check_in_time','<=',date('Y-m-d'))->where('u_id',$value->id)/*->orderBy('check_in_time','ASC')*/->get();

                    $mileage_amount = 0;
                    $gps_mileage = 0;
                    foreach($query as $result){

                         $checkIn = $result->check_in_time;
                         $checkOut = $result->check_out_time;

                         if(isset($checkOut)){
                              $gps_mileage += $this->getMileage($table_name,$checkIn,$checkOut,$result->user->id);
                         }
                    }

               // }

               $private_allow = 0;
               $station_sum_allow = 0;

               $station_sum = $itineraryDates->sum('e_mileage');

               $user_details = AppUser::where('id',$value->id)->first();
               $private_mileage = $user_details->u_pvt_mileage_limit;

               $mileage_km = $itineraryDates->sum('mileage');
               $mileage_allow = $itineraryDates->sum('mileage_value');
               $lastItinerary = $itineraryDates->last();

               if($lastItinerary){
                    $private_allow = $private_mileage*$lastItinerary['vehicleTypeRate'];
                    $station_sum_allow = $station_sum*$lastItinerary['vehicleTypeRate'];
               }

               $total_mileage += $mileage_km;
               $total_mileage_allow += $mileage_allow;
               $total_primileage += $private_mileage;
               $total_primileage_allow += $private_allow;

               $total_e_mileage += $station_sum;
               $total_e_mileage_allow += $station_sum_allow;
               $total_e_primileage += $private_mileage;
               $total_e_primileage_allow += $private_allow;
               $tot_mileage_km += round($gps_mileage);

               // $formatedresulst [] = [
               //      'team_name' => $value->tm_name,
               //      'agent' => $value->name,

               //      'p_m_km' => $mileage_km,
               //      'p_m_allow' => $mileage_allow,
               //      'p_pvt_m_km' => $private_mileage?$private_mileage:0,
               //      'p_pvt_m_allow' => $private_allow,

               //      'e_m_km' => $station_sum,
               //      'e_m_allow' => round($station_sum_allow,2),
               //      'e_pvt_m_km' => $private_mileage?$private_mileage:0,
               //      'e_pvt_m_allow' => $private_allow,
               //      'mileage_amount' => round($gps_mileage,2)
               // ];

                    $rowNew['team_name'] = $value->tm_name;
                    $rowNew['agent'] = $value->name;

                    $rowNew['p_m_km'] = round($mileage_km,2);
                    $rowNew['p_m_allow'] = round($mileage_allow,2);
                    $rowNew['p_pvt_m_km'] = $private_mileage?$private_mileage:0;
                    $rowNew['p_pvt_m_allow'] = round($private_allow,2);

                    $rowNew['e_m_km'] = round($station_sum,2);
                    $rowNew['e_m_allow'] = round($station_sum_allow,2);
                    $rowNew['e_pvt_m_km'] = $private_mileage?$private_mileage:0;
                    $rowNew['e_pvt_m_allow'] = round($private_allow,2);
                    $rowNew['mileage_amount'] = round($gps_mileage,2);

                    $formatedresulst[] = $rowNew;

          }
          // var_dump($total_primileage_allow);die;
          $formatedresulst [] = [
               'team_name' => 'Page Total',
               'agent' => '',

               'p_m_km' => round($total_mileage,2),
               'p_m_allow' => round($total_mileage_allow,2),
               'p_pvt_m_km' =>  number_format($total_primileage,2),
               'p_pvt_m_allow' => round($total_primileage_allow,2),

               'e_m_km' => round($total_e_mileage,2),
               'e_m_allow' => round($total_e_mileage_allow,2),
               'e_pvt_m_km' => number_format($total_e_primileage,2),
               'e_pvt_m_allow' => round($total_e_primileage_allow,2),
               'mileage_amount'=> round($tot_mileage_km,2),
               'special'=>true
          ];

          return[
               'count'=> $count_new,
               'results'=> $formatedresulst
          ];
     }

     protected function formatMileageDetails($model,$namespace,$columnPrefix){
          $mileage = 0;
          $bataValue = 0;
          $has = false;
          $bataId = 0;

          if(isset($model->{$namespace})){
              $has = true;

              $mileage = $model->{$namespace}->{$columnPrefix."_mileage"};

              if(isset($model->{$namespace}->bataType)){
                  $bataValue = $model->{$namespace}->bataType->bt_value;
                  $bataId = $model->{$namespace}->bataType->bt_id;
              }
          }

          return [
              "mileage"=>$mileage,
              "bataValue"=>$bataValue,
              "has"=>$has,
              "bataTypeId"=>$bataId
          ];
     }

     protected function getAdditionalHeaders($request){
          $columns = [[
              [
                  "title"=>"",
                  "colSpan"=>2
              ],
              [
                  "title"=>"Planned",
                  "colSpan"=>4
              ],
              [
                  "title"=>"Executed",
                  "colSpan"=>5
              ]
          ]];
          return $columns;
     }

     public function getMileage($table_name,$checkIn,$checkOut,$u_id){
          $mileage = 0;

          try {
              $gps_for_day = DB::table($table_name)
                      ->select('gt_lon','gt_lat','gt_time')
                      ->where('u_id',$u_id)
                      ->whereBetween('gt_time',[$checkIn->format('Y-m-d 00:00:00'),$checkOut->format('Y-m-d 23:59:59')])
                      ->orderBy('gt_time','desc')
                      ->get();


              for ($i=0; $i < $gps_for_day->count()-2; $i++) {
                  $point1 = array("lat" => $gps_for_day[$i]->gt_lat, "long" => $gps_for_day[$i]->gt_lon);
                  $point2 = array("lat" => $gps_for_day[$i+1]->gt_lat, "long" => $gps_for_day[$i+1]->gt_lon);

               //    $mileage += $this->distanceCalculation($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
                  $mileage += $this->distance($point1['lat'], $point1['long'], $point2['lat'], $point2['long'],"K");
              }
          } catch (\Exception $e) {

          }
          return json_encode($mileage);
     }

     function distance($lat1, $lon1, $lat2, $lon2, $unit) {
          if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
          }
          else {
               $theta = $lon1 - $lon2;
               $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
               $dist = acos($dist);
               $dist = rad2deg($dist);
               $miles = $dist * 60 * 1.1515;
               $unit = strtoupper($unit);

               if ($unit == "K") {
                    return ($miles * 1.609344);
               } else if ($unit == "N") {
                    return ($miles * 0.8684);
               } else {
                    return $miles;
               }
          }
     }

     protected function setColumns(ColumnController $columnController,Request $request){

          $columnController->text('team_name')->setLabel("Team");
          $columnController->text('agent')->setLabel("Agent");

          $columnController->number('p_m_km')->setLabel("Mileage KM");
          $columnController->number('p_m_allow')->setLabel("Mileage Allowance");
          $columnController->number('p_pvt_m_km')->setLabel("Pvt Mileage KM");
          $columnController->number('p_pvt_m_allow')->setLabel("Pvt Mileage Allowance");

          $columnController->number('e_m_km')->setLabel("Mileage KM");
          $columnController->number('e_m_allow')->setLabel("Mileage Allowance");
          $columnController->number('e_pvt_m_km')->setLabel("Pvt Mileage KM");
          $columnController->number('e_pvt_m_allow')->setLabel("Pvt Mileage Allowance");
          $columnController->number('mileage_amount')->setLabel("Gps Mileage KM");

     }
     protected function setInputs($inputController){
          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setLink("team")->setValidations('');
          $inputController->ajax_dropdown('user')->setWhere(['u_tp_id'=> '3'.'|'.config('shl.product_specialist_type')])->setWhere(["tm_id" => "{team_id}",'divi_id'=>"{divi_id}"])->setLabel('PS/MR or FM')->setLink('user')->setValidations('');
          $inputController->ajax_dropdown('divi_id')->setLabel('Division')->setLink('division')->setValidations('');
          $inputController->date("s_date")->setLabel("Month")->setValidations('');
          $inputController->setStructure([
               ["team_id","user"],["divi_id","s_date"]
               ]);
     }
}
?>
