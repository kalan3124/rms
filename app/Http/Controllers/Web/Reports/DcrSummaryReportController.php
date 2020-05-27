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
use App\Models\MrDoctorCreaton;
use App\Models\DoctorSpeciality;
use App\Models\UserCustomer;
use App\Models\Doctor;
use App\Models\ProductiveVisit;
use App\Models\User;
use App\Traits\Territory;
use App\Models\SubTown;
use Illuminate\Support\Facades\Auth;
use App\Models\Chemist;
use App\Models\OtherHospitalStaff;
use App\Models\ChemistMarketDescription;
use App\Models\HospitalStaffCategory;
use App\Models\ItineraryDate;
use App\Models\TeamUser;

class DcrSummaryReportController extends ReportController {
     use Territory;
     protected $title = "DCR Summary Report";

     public function search(Request $request){
          $validations = Validator::make($request->all(),[
               'values'=>'required|array',
               'values.s_date'=>'required|date'
          ]);

          if($validations->fails()){
               throw new WebAPIException($validations->errors()->first());
          }
          
          $values = $request->input('values');

          $formatedresulst = [];

          $pro_table = DB::table('productive_visit AS p')
                    ->select('t.tm_id','p.pro_start_time','u.id','u.name','t.tm_name','p.doc_id','d.divi_name','p.chemist_id','p.hos_stf_id','t.fm_id',DB::raw('"1" AS status'))
                    ->join('users AS u','p.u_id','u.id')
                    ->join('team_users AS tm','tm.u_id','u.id')
                    ->join('teams AS t','tm.tm_id','t.tm_id')
                    ->join('division AS d','d.divi_id','u.divi_id')
                    ->where('u.id','!=',2)
                    ->where('u.id','!=',5)
                    ->whereDate('p.pro_start_time',">=",date("Y-m-d",strtotime($values['s_date'])))
                    ->whereDate('p.pro_start_time',"<=",date("Y-m-d",strtotime($values['e_date'])));

          $unpro_table = DB::table('unproductive_visit AS un')
                    ->select('t.tm_id','un.unpro_time','u.id','u.name','t.tm_name','un.doc_id','d.divi_name','un.chemist_id','un.hos_stf_id','t.fm_id',DB::raw('"0" AS status'))
                    ->join('users AS u','un.u_id','u.id')
                    ->join('team_users AS tm','tm.u_id','u.id')
                    ->join('teams AS t','tm.tm_id','t.tm_id')
                    ->join('division AS d','d.divi_id','u.divi_id')
                    ->where('u.id','!=',2)
                    ->where('u.id','!=',5)
                    ->whereDate('un.unpro_time',">=",date("Y-m-d",strtotime($values['s_date'])))
                    ->whereDate('un.unpro_time',"<=",date("Y-m-d",strtotime($values['s_date'])));

          if (isset($values['team_id'])) {
               $pro_table->where('t.tm_id',$request->input('values.team_id.value'));
               $unpro_table->where('t.tm_id',$request->input('values.team_id.value'));
          }
   
          if (isset($values['user'])) {
               $pro_table->where('tm.u_id',$request->input('values.user.value'));
               $unpro_table->where('tm.u_id',$request->input('values.user.value'));
          }

          if (isset($values['div_name'])) {
               $pro_table->where('d.divi_id',$request->input('values.div_name.value'));
               $unpro_table->where('d.divi_id',$request->input('values.div_name.value'));
          }

          $query = $pro_table->union($unpro_table)->orderBy('pro_start_time');
          $count = $this->paginateAndCount($query,$request,'tm_id');

          $results = $query->get();

          $results->transform(function($data){
               $data->date = date('Y-m-d',strtotime($data->pro_start_time));
               return $data;
          });

          // $datesCount = 0;
          // $datesCounts = 0;
          foreach ($results as $key => $row) {
               $datesCount =  $results->where('date',date('Y-m-d',strtotime($row->pro_start_time)))->count();
      
               if($key){
                    $date = date('Y-m-d',strtotime($row->pro_start_time));
                    $prevRow = $results[$key-1];
                    $prevDate = date('Y-m-d',strtotime($prevRow->pro_start_time));

                    if($date != $prevDate){
                         $rowNew['date'] = date('Y-m-d',strtotime($row->pro_start_time));
                         $rowNew['date_rowspan'] = $datesCount;
                    } else {
                         $rowNew['date'] = null;
                         $rowNew['date_rowspan'] = 0;
                    }

               } else {
                    $rowNew['date'] = date('Y-m-d',strtotime($row->pro_start_time));
                    $rowNew['date_rowspan'] = $datesCount;
               }

               $fm_name = User::find($row->fm_id);
               
               $fm_name_ = "";

               $itinerary = Itinerary::where('rep_id',$row->id)->where('i_year',date('Y',strtotime($values['s_date'])))->where('i_month',date('m',strtotime($values['s_date'])))->whereNotNull('i_aprvd_at')->latest()->first();
               $join_fm_itinerary = ItineraryDate::where('i_id',$itinerary['i_id'])->get();
          
               foreach ($join_fm_itinerary as $key => $value) {
                    $itinerary_ = Itinerary::where('fm_id',$fm_name['id'])->first();
                    $join_fm = ItineraryDate::where('i_id',$itinerary_['i_id'])->where('id_date',$value['id_date'])->where('u_id',$row->id)->first();
                    if($join_fm)
                         $fm_name_ = $fm_name['name'];
               }
               
               $doctor = null;
               $chemist = null;
               $otherHospitalStaff = null;

               $doctor_type = null;
               $chemist_type = null;
               $otherHospitalStaff_type = null;

               $doctor_sp = null;
               $chemist_sp = null;
               $otherHospitalStaff_sp = null;

               $status = "";
               $doc_status = "";
               $chemist_status = "";
               $otherhospital_status = "";

               $pro_visit = ProductiveVisit::query();
               $pro_visit->where('u_id',$row->id);

               if(isset($row->doc_id)){
                    $doc_query = Doctor::where('doc_id',$row->doc_id)->first();

                    if(isset($doc_query->doc_spc_id))
                         $doctor_sp = DoctorSpeciality::where('doc_spc_id',$doc_query->doc_spc_id)->first();

                    if(isset($doc_query->doc_name))
                         $doctor = $doc_query->doc_name;

                    $doctor_type = "Doctor";

                    $pro_doc = $pro_visit->where('doc_id',$row->doc_id)->first();
                    if($pro_doc){
                         $doc_status = "Met";
                    } else {
                         $doc_status = "Missed";
                    }
               }

               if(isset($row->chemist_id)){
                    $chemist_query = Chemist::where('chemist_id',$row->chemist_id)->first();

                    if(isset($chemist_query->chemist_mkd_id))
                         $chemist_sp = ChemistMarketDescription::where('chemist_mkd_id',$chemist_query->chemist_mkd_id)->first();

                    if($chemist_query->chemist_name)
                         $chemist = $chemist_query->chemist_name;

                    $chemist_type = "Chemist";

                    $pro_chemist = $pro_visit->where('chemist_id',$row->chemist_id)->first();
                    if($pro_chemist){
                         $chemist_status = "Met";
                    } else {
                         $chemist_status = "Missed";
                    }
               }

               if(isset($row->hos_stf_id)){
                    $otherHos_query = OtherHospitalStaff::where('hos_stf_id',$row->hos_stf_id)->first();

                    if(isset($otherHos_query->hos_stf_cat_id))
                         $otherHospitalStaff_sp = HospitalStaffCategory::where('hos_stf_cat_id',$otherHos_query->hos_stf_cat_id)->first();

                    if(isset($otherHos_query->hos_stf_name))
                         $otherHospitalStaff = $otherHos_query->hos_stf_name;

                    $otherHospitalStaff_type = "OHS";

                    $pro_other = $pro_visit->where('hos_stf_id',$row->hos_stf_id)->first();
                    if($pro_other){
                         $otherhospital_status = "Met";
                    } else {
                         $otherhospital_status = "Missed";
                    }
               }

               $mr = User::find($row->id);
               try {
                    $subTownsToday = $this->getTerritoriesByItinerary($mr,strtotime($row->pro_start_time));
               } catch (\Throwable $exception) {
                    $subTownsToday = collect();
               }

               $itinerarySubTowns = [];
          
               if($subTownsToday->isEmpty()){
                    $itinerarySubTowns = [];
               } else {
                    $itinerarySubTowns = $subTownsToday->pluck('sub_twn_id')->all();
               }

               $subTowns = SubTown::whereIn('sub_twn_id',$itinerarySubTowns)->select('*')->get();

               $subTowns->transform(function($subTown){
                    if(isset($subTown)){
                         return $subTown->sub_twn_name;
                    }
               });
               $subTownNames = implode('/ ',$subTowns->all());

               $rowNew['test'] = $fm_name_?$fm_name_:null;
               $rowNew['date'] = date('Y-m-d',strtotime($row->pro_start_time));
               $rowNew['team_name'] =$row->tm_name;
               $rowNew['fm_name'] = $fm_name_?$fm_name_:'';
               $rowNew['mr_name'] = $row->name;
               $rowNew['town'] = isset($subTownNames)?$subTownNames:"-";
               $rowNew['divi_name'] = $row->divi_name;
               $rowNew['type'] = (isset($doctor_type)?$doctor_type:(isset($chemist_type)?$chemist_type:$otherHospitalStaff_type));
               $rowNew['doc_chemist_other'] = (isset($doctor)?$doctor:(isset($chemist)?$chemist:$otherHospitalStaff));
               $rowNew['speciality'] = (isset($doctor_sp)?$doctor_sp->speciality_name:(isset($chemist_sp->chemist_mkd_name)?$chemist_sp->chemist_mkd_name:''));
               $rowNew['status'] = (isset($doc_status)?$doc_status:(isset($chemist_status)?$chemist_status:isset($otherhospital_status)?$otherhospital_status:''));

               $formatedresulst[] = $rowNew;
          }

          return[
               'count'=>$count,
               'results'=> $formatedresulst
          ];
     }

     protected function setColumns(ColumnController $columnController,Request $request){
          $columnController->text('date')->setLabel("Date");
          $columnController->text('divi_name')->setLabel("Division");
          $columnController->text('team_name')->setLabel("Team Name");
          $columnController->text('mr_name')->setLabel("MR Name");
          $columnController->text('fm_name')->setLabel("Joined FM");
          $columnController->text('town')->setLabel("Town");
          $columnController->text('type')->setLabel("Type");
          $columnController->text('doc_chemist_other')->setLabel("Doctor/Chemist/ Other Staff");
          $columnController->text('speciality')->setLabel("Speciality");
          $columnController->text('status')->setLabel("Status");
     }

     protected function setInputs($inputController){
          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setLink("team")->setValidations('');
          $inputController->ajax_dropdown('user')->setWhere(["tm_id" => "{team_id}",'u_tp_id'=> "2|3".'|'.config('shl.product_specialist_type')])->setLabel('PS/MR or FM')->setLink('user')->setValidations('');
          $inputController->ajax_dropdown("div_name")->setLabel("Division")->setLink("division")->setValidations('');
          $inputController->date("s_date")->setLabel("From");
          $inputController->date("e_date")->setLabel("To");

          $inputController->setStructure([
               ["team_id","user","div_name"],
               ["s_date","e_date"]
          ]);
     }
}
?>