<?php
namespace App\Http\Controllers\Web\Reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\Itinerary;
use App\Models\UserAttendance;
use App\Form\Columns\ColumnController;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\SpecialDay;
use App\Models\User;

class DcrExecuteSummaryReportController extends ReportController {
     protected $title = "DCR Execute Summary Report";

     public function search(Request $request){
          $validations = Validator::make($request->all(),[
               'values'=>'required|array',
               'values.s_date'=>'required|date'
          ]);

          if($validations->fails()){
               throw new WebAPIException($validations->errors()->first());
          }
          
          $values = $request->input('values');

          $query = Team::query();
          $query->with(['teamUsers','teamUsers.user','user','user.division']);

          if($request->has('values.team_id.value')){
               $query->where('tm_id',$request->input('values.team_id.value'));
          }


          $results = $query->get();

          $formatedresulst = [];

          foreach($results as $row){

                $d_name = null;
                
               foreach($row->teamUsers as $data){

                    $user = null;
                    $d_name ="";
                    $date_count = 0;
                    $inComplete = 0;

                    $latestItineraryByRep = Itinerary::where('rep_id',$data->user['id'])
                                             ->whereNotNull('i_aprvd_at')
                                             ->where('i_year','=',date("Y",strtotime($values['s_date'])))
                                             ->where('i_month','=',date("m",strtotime($values['s_date'])))
                                             ->latest()
                                             ->first(); 

                    $s_dates = SpecialDay::whereYear('sd_date',date("Y",strtotime($values['s_date'])))->whereMonth('sd_date',date("m",strtotime($values['s_date'])))->select('*',DB::raw('DATE(sd_date) as formatteddate'))->get();

                    $begin = new \DateTime(date('Y-m-01'));
                    $end = new \DateTime(date("Y-m-t"));
                    $end = $end->modify('1 day'); 

                    $interval = new \DateInterval('P1D');
                    $daterange = new \DatePeriod($begin, $interval ,$end);

                    $sun_count = 0;
                    $sat_count = 0;
                    $sp_count = 0;
                    
                    foreach ($daterange as $key => $date) {

                         $sp_date = $s_dates->where('formatteddate',$date->format('Y-m-d'))->first();

                         if($date->format('D') == "Sat"){
                              $sat_count++;
                         } elseif ($date->format('D') == "Sun"){
                              $sun_count++;
                         } elseif ($sp_date) {
                              $sp_count++;
                         }
                         
                    }

                    $dates = date('t',strtotime($values['s_date']));
                    $planned = $dates - ($sat_count+$sun_count+$sp_count);

                    // $planned = DB::table('itinerary_date AS id')  
                    //      ->where('id.i_id',$latestItineraryByRep['i_id'])
                    //      ->count(); 

                    $workingDays = DB::table('itinerary_date AS id')
                         ->join('itinerary_day_type AS idy','idy.id_id','id.id_id')
                         ->join('day_type AS dt','idy.dt_id','dt.dt_id')
                         ->where('id.i_id',$latestItineraryByRep['i_id'])
                         ->where('dt.dt_is_working','1')
                         ->groupBy('id.id_date')
                         ->get()
                         ->pluck('id_date')
                         ->all();

                    $workingDaysCount = count($workingDays);


                    $inprogress = UserAttendance::where('u_id',$data->user['id'])
                                   ->whereNotNull('check_in_time')
                                   ->whereNull('check_out_time')
                                   ->whereMonth('check_in_time',date("m",strtotime($values['s_date'])))
                                   ->whereYear('check_out_time',date("Y",strtotime($values['s_date'])))
                                   ->count();
                                   
                    $attendance_new = UserAttendance::where('u_id',$data->user['id'])
                                   ->whereYear('check_in_time',date("Y",strtotime($values['s_date'])))
                                   ->whereMonth('check_in_time',date("m",strtotime($values['s_date'])))
                                   ->whereYear('check_out_time',date("Y",strtotime($values['s_date'])))
                                   ->whereMonth('check_out_time',date("m",strtotime($values['s_date'])))
                                   ->select(['check_in_time','check_out_time'])
                                   ->get();                                   
                                   
                    $attendance_new->transform(function($data){

                         $attendance = 
                              [
                                   "from"=> date('d',strtotime([$data->check_in_time][0])),
                                   "to"=> date('d',strtotime([$data->check_out_time][0]))
                              ]
                         ;
                                        
                         return $attendance;

                    });  
                    
                    $attendance = $attendance_new->all();

                    $dates = [];
               
                    foreach($attendance as $att){
                    
                         for($i=$att["from"];$i<=$att["to"];$i++){
                              if(!in_array($i,$dates)&&in_array($i,$workingDays))
                                   $dates[] = $i;
                         }       
                         
                    }

                    $date_count = count($dates);
                    $inComplete = $workingDaysCount - ($date_count + $inprogress);
                    

                    if($data->user){
                         $user = [
                              "id" => $data->user->id,
                              "label"=>$data->user->name,
                              "value"=>$data->user->getKey()
                         ];

                         $hod_s = Team::where('tm_id',$row->tm_id)->first();

                         if(isset($hod_s))
                              $hod_s_manager = User::where('id',$hod_s->hod_id)->first();

                         if($data->user->division){
                              if((($request->has('values.user.value') && $request->input('values.user.value') == $data->user['id']) || !$request->input('values.user.value')) && (($request->has('values.div_name.value') && $request->input('values.div_name.value') == $data->user->division->divi_id) || !$request->input('values.div_name.value'))){
                                   $d_name = $data->user->division->divi_name;
                                   $formatedresulst[]= [
                                        'team_name' =>$row->tm_name,
                                        's_manager' =>$hod_s_manager?$hod_s_manager->name:"-",
                                        'hod_name' =>$hod_s_manager?$hod_s_manager->name:"-",
                                        'fm_name' => $row->user['name'],
                                        'mr_name' => $data->user['name'],
                                        'fm_mr_code' => $data->user['u_code'],
                                        'divi_name' => $d_name,
                                        'planned_days' => $planned,
                                        'fworking_days' => $workingDaysCount,
                                        'completed_days' => $date_count,
                                        'inprogress_days' => $inprogress,
                                        'dcr_not_sub_days' => ($inprogress + $inComplete)//$workingDaysCount - $date_count
                                   ];
                              }
                         }
                    }

               }

          }

          return[
               'count'=>0,
               'results'=> $formatedresulst
          ];
     }

     protected function setColumns(ColumnController $columnController,Request $request){
          $columnController->text('divi_name')->setLabel("Division");
          $columnController->text('s_manager')->setLabel("Senior Manager");
          $columnController->text('hod_name')->setLabel("HOD Name");
          $columnController->text('team_name')->setLabel("Team Name");
          $columnController->text('fm_name')->setLabel("FM Name");
          $columnController->text('mr_name')->setLabel("MR Name");
          $columnController->text('fm_mr_code')->setLabel("FM/MR Code");
          $columnController->text('planned_days')->setLabel("Planned Days");
          $columnController->text('fworking_days')->setLabel("Field Working Days");
          $columnController->text('completed_days')->setLabel("Completed Days");
          $columnController->text('inprogress_days')->setLabel("Inprogress Days");
          $columnController->text('dcr_not_sub_days')->setLabel("DCR Not Submit Days");
     }

     protected function setInputs($inputController){
          $inputController->ajax_dropdown("div_name")->setLabel("Division")->setLink("division")->setValidations('');
          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setWhere(['divi_id'=>"{div_name}"])->setLink("team")->setValidations('');
          $inputController->ajax_dropdown('user')->setWhere(["tm_id" => "{team_id}",'u_tp_id'=>"2|3".'|'.config('shl.product_specialist_type') ,'divi_id'=>"{div_name}"])->setLabel('PS/MR or FM')->setLink('user')->setValidations('');
          $inputController->date("s_date")->setLabel("Month");

          $inputController->setStructure([
               ["div_name","team_id","user"],
               ["s_date"]
          ]);
     }
}
?>