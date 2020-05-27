<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Form\DayType as DayTypeForm;
use App\Models\AdditionalRoutePlan;
use App\Models\AdditionalRoutePlanArea;
use App\Models\DayType;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\ItineraryDayType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Team;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Expenses;
use App\Traits\Territory;
use App\Models\SpecialDay;
use App\Models\StandardItineraryDate;
use App\Models\StationMileage;
use App\Models\TeamUser;
use App\Models\UserItinerarySubTown;
use App\Models\UserAttendance;
use App\Models\Area;
use App\Models\EmailSelectedType;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ItineraryController extends Controller
{
    use Territory;
    public function load(Request $request)
    {
        // Filtering user inputs
        $medicalRep = $request->input('mr');
        $fieldManager = $request->input('fm');
        $yearMonth = explode('-', $request->input('yearMonth'));

        // Seperate the year and month
        $year = $yearMonth[0];
        $month = $yearMonth[1];

        if(!isset($fieldManager)&&!isset($medicalRep))
            throw new WebAPIException("Please provide a medical representaive or field manager");

        $user = isset($medicalRep)?$medicalRep['value']:$fieldManager['value'];

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
                'itineraryDates.additionalRoutePlan.areas',
                'itineraryDates.additionalRoutePlan.areas.subTown',
                'itineraryDates.itineraryDayTypes',
                'itineraryDates.itineraryDayTypes.dayType',
                'itineraryDates.standardItineraryDate',
                'itineraryDates.standardItineraryDate.bataType',
                'itineraryDates.changedItineraryDate',
                'itineraryDates.changedItineraryDate.areas',
            ]);

        if(isset($medicalRep)){
            $query->where('rep_id',$medicalRep['value']);
        }

        if(isset($fieldManager)){
            $query->where('fm_id',$fieldManager['value']);
        }

        $itinerary = $query->first();

        // Aborting if itinerary not found

        $specialDays = SpecialDay::whereYear('sd_date',$year)->whereMonth('sd_date',$month)->get();

        $specialDays->transform(function($specialDay){
            return [
                'date'=>date('d',strtotime($specialDay->sd_date)),
                'special'=>$specialDay->sd_name,
                "types"=>[]
            ];
        });

        if($itinerary){
            // Formating the itinerary dates
            $itinerary->itineraryDates->transform(function ($date)use($year,$month,$specialDays) {

                // Formating the itinerary day types
                $date->itineraryDayTypes->transform(function ($itineraryDayType) {
                    return (string) $itineraryDayType->dayType->dt_id;
                });

                $description = null;
                $additionalValues = null;
                $joinFieldWorker = null;
                $otherDay = null;
                $mileage = 0.00;
                $bataTypeName = "Not Set";
                $changedDate = null;

                if($date->changedItineraryDate){
                    $routePlan = $date->changedItineraryDate;
                    $changedDate = [];
                    $changedDate['description'] = "Changed Itinerary.";
                    $changedDate['mileage'] = $routePlan->idc_mileage;
                    $mileage = $changedDate['mileage'];

                    if ($routePlan->bataType) {
                        $changedDate['bata'] = [
                            'label' => $routePlan->bataType->bt_name,
                            'value' => $routePlan->bataType->getKey(),
                        ];
                        $bataTypeName = $routePlan->bataType->bt_name;
                    }
                    $changedDate['areas'] = [];
                    if (!$routePlan->areas->isEmpty()) {
                        $formatedAreas = $routePlan->areas->transform(function ($area) {
                            if(!isset($area->subTown))
                                return null;

                            return [
                                'label'=>$area->subTown->sub_twn_name,
                                'value'=>$area->subTown->getKey()
                            ];
                        })
                        ->filter(function ($area){ return !!$area;})
                        ->toArray();

                        $changedDate['areas']=$formatedAreas;
                    }
                }
                else if ($date->additionalRoutePlan) {
                    $routePlan = $date->additionalRoutePlan;
                    $additionalValues = [];
                    $additionalValues['description'] = $routePlan->arp_description;
                    $additionalValues['mileage'] = $routePlan->arp_mileage;
                    $mileage = $additionalValues['mileage'];

                    if ($routePlan->bataType) {
                        $additionalValues['bata'] = [
                            'label' => $routePlan->bataType->bt_name,
                            'value' => $routePlan->bataType->getKey(),
                        ];
                        $bataTypeName = $routePlan->bataType->bt_name;
                    }
                    $additionalValues['areas'] = [];
                    if (!$routePlan->areas->isEmpty()) {
                        $formatedAreas = $routePlan->areas->transform(function ($area) {
                            if(!isset($area->subTown))
                                return null;

                            return [
                                'label'=>$area->subTown->sub_twn_name,
                                'value'=>$area->subTown->getKey()
                            ];
                        })
                        ->filter(function ($area){ return !!$area;})
                        ->toArray();

                        $additionalValues['areas']=$formatedAreas;
                    }

                }
                else if ($date->standardItineraryDate) {
                    $description = [
                        'label' => $date->standardItineraryDate->sid_description,
                        'value' => $date->standardItineraryDate->getKey(),
                    ];
                    $mileage = $date->standardItineraryDate->sid_mileage;
                    if(isset($date->standardItineraryDate->bataType))
                        $bataTypeName = $date->standardItineraryDate->bataType->bt_name;
                }
                else if($date->joinFieldWorker){

                    try{
                        $subTowns = $this->getTerritoriesByItinerary($date->joinFieldWorker,strtotime(str_pad($year,4,'0',STR_PAD_LEFT)."-".str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($date->id_date,2,'0',STR_PAD_LEFT)));

                        $subTowns->transform(function($subTown){
                            return $subTown->sub_twn_name;
                        });

                        $subTownNames = implode(", ", $subTowns->toArray());

                    } catch(\Exception $e){
                        $subTownNames = "";
                    }

                    $joinFieldWorker = [];
                    $joinFieldWorker['jointFieldWorker']=[
                        "value"=>$date->joinFieldWorker->getKey(),
                        "label"=>$date->joinFieldWorker->getName()." - ".$subTownNames
                    ];

                    $joinFieldWorker['mileage']= $date->id_mileage;
                    $joinFieldWorker['bataType'] = isset($date->bataType)?[
                        "value"=>$date->bataType->getKey(),
                        'label'=>$date->bataType->bt_name
                    ]:null;
                    $mileage = $date->id_mileage;
                    if(isset($date->bataType))
                        $bataTypeName = $date->bataType->bt_name;
                } else {
                    $otherDay = [
                        "mileage"=>$date->id_mileage,
                        'bataType'=>isset($date->bataType)?[
                            'value'=>$date->bataType->getKey(),
                            'label'=>$date->bataType->bt_name
                        ]:null
                    ];
                    $mileage = $date->id_mileage;

                    if(isset($date->bataType)){
                        $bataTypeName = $date->bataType->bt_name;
                    }
                }

                $specialDay =  $specialDays->where('date',$date->id_date)->first();
                // Returning
                return [
                    'date' => $date->id_date,
                    'description' => $description,
                    'types' => $date->itineraryDayTypes,
                    'additionalValues' => $additionalValues,
                    'joinFieldWorker'=>$joinFieldWorker,
                    "changedRoute"=>$changedDate,
                    'otherDay'=>$otherDay,
                    'special'=>$specialDay?$specialDay['special']:null,
                    "mileage"=>$mileage,
                    "bataTypeName"=>$bataTypeName
                ];
            });

            $itineraryDates = $itinerary->itineraryDates;
        } else {
            $itineraryDates = collect([]);
        }

        foreach($specialDays as $specialDay){
            $itineraryDate = $itineraryDates->where('date',$specialDay['date'])->first();

            if(!$itineraryDate){
                $itineraryDates->push($specialDay);
            }
        }

        $attendance = UserAttendance::where('u_id',$user)->whereYear('check_in_time',$year)->whereMonth('check_in_time',$month)->groupBy(DB::raw('DATE(check_in_time)'))->select(DB::raw('DATE(check_in_time) AS `date`'))->get();
        $expenses = Expenses::where('u_id',$user)->whereYear('exp_date',$year)->whereMonth('exp_date',$month)->groupBy(DB::raw('DATE(exp_date)'))->select(DB::raw('DATE(exp_date) AS `date`'))->get();
        $stationMileage = StationMileage::where('u_id',$user)->whereYear('exp_date',$year)->whereMonth('exp_date',$month)->groupBy(DB::raw('DATE(exp_date)'))->select(DB::raw('DATE(exp_date) AS `date`'))->get();

        $itineraryDates->transform(function($itineraryDate) use($year,$month,$attendance,$expenses,$stationMileage){
            $date = date('Y-m-d',strtotime($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($itineraryDate['date'],2,'0',STR_PAD_LEFT)));

            $attendanceForDay = $attendance->where('date',$date)->first();
            $expenseForDay = $expenses->where('date',$date)->first();
            $stationMileageForDay = $stationMileage->where('date',$date)->first();

            $itineraryDate['forbidden'] = $attendanceForDay&&($expenseForDay||$stationMileageForDay);

            return $itineraryDate;
        });

        $begin = new \DateTime( date('Y-m-d',strtotime($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-01')) );
        $end   = new \DateTime( date('Y-m-t',strtotime($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-01')) );

        for($i = $begin; $i <= $end; $i->modify('+1 day')){
            $date =  $i->format("Y-m-d");
            $intDate = $i->format('d');

            $attendanceForDay = $attendance->where('date',$date)->first();
            $expenseForDay = $expenses->where('date',$date)->first();
            $stationMileageForDay = $stationMileage->where('date',$date)->first();

            if($attendanceForDay&&($expenseForDay||$stationMileageForDay)){
                $itineraryDate = $itineraryDates->where('date',$intDate)->first();

                if(!$itineraryDate){
                    $itineraryDates->push([
                        'date'=>$intDate,
                        'forbidden'=>true,
                        'types'=>[]
                    ]);
                }
            }
        }

        return response()->json([
            'dates'=>$itineraryDates,
            'approved'=> $itinerary? isset($itinerary->i_aprvd_at):false
        ]);
    }

    public function dayTypes()
    {
        $dayTypes = DayType::get();

        $dayTypeForm = new DayTypeForm();
        $colors = $dayTypeForm->getInputController()->getInput('dt_color')->getCustomProps()['options'];

        $dayTypes->transform(function ($dayType) use ($colors) {

            return [
                'color' => $colors[$dayType->dt_color],
                'label' => $dayType->dt_code,
                'value' => $dayType->getKey(),
                'bata' => !!$dayType->dt_bata_enabled,
                'mileage' => !!$dayType->dt_mileage_enabled,
                'working' => !!$dayType->dt_is_working,
                'fieldWorking'=>!!$dayType->dt_field_work_day
            ];

        });

        return $dayTypes;
    }

    public function saveToDB(Request $request)
    {
        $dates = $request->input('dates', []);
        $yearMonth = $request->input('yearMonth');
        $medicalRep = $request->input('mr');
        $fieldManager = $request->input('fm');

        if (!isset($medicalRep['value']) && !isset($fieldManager['value'])) {
            throw new WebAPIException("Medical representative or field manager is required.");
        }

        if (!strtotime($yearMonth . '-01')) {
            throw new WebAPIException('Year Month not found');
        }

        $itineraryYear = date('Y', strtotime($yearMonth . '-01'));
        $itineraryMonth = date('m', strtotime($yearMonth . '-01'));
        $year = date('Y');
        $month = date('m');

        $teamUser  = TeamUser::with('team')->where('u_id',$medicalRep['value'])->first();

        if(isset($fieldManager)){
            $team = Team::where('fm_id',$fieldManager['value'])->first();
            $user = User::find($fieldManager['value']);
        }

        if(isset($teamUser)&&$teamUser->team){
            $team = $teamUser->team;
            $user = User::find($teamUser->u_id);
        }

        if(! $team){
            throw new WebAPIException("You haven't team.",21);
        }

        if(isset($teamUser)){
            $parent = User::find($team->fm_id);
        }

        if(isset($fieldManager)){
            $parent = User::find($team->hod_id);
        }

        if($itineraryYear<=$year&&$itineraryMonth<=$month-2){
            throw new WebAPIException("Can not update passed itineraries.");
        }

        if($itineraryYear<=$year&&$itineraryMonth<=$month-1&&strtotime($team->tm_exp_block_date)>=time()){
            throw new WebAPIException("Can not update passed itineraries.");
        }

        DB::beginTransaction();

        try {

            $itinerary = Itinerary::create([
                'i_year' => $itineraryYear,
                'i_month' => $itineraryMonth,
                'rep_id' => isset($medicalRep['value']) ? $medicalRep['value'] : null,
                'fm_id' => isset($fieldManager['value']) ? $fieldManager['value'] : null,
            ]);


            $emailDates = [];

            $specialDays = SpecialDay::where('sd_date','LIKE',"$yearMonth%")->get();
            $specialDays->transform(function($specialDay) use(&$emailDates) {
                $emailDates[date('d',strtotime($specialDay->sd_date))] =[
                    'date'=> $specialDay->sd_date,
                    'description'=> "Holiday - ".$specialDay->sd_name
                ];

                return [
                    'date'=>date('d',strtotime($specialDay->sd_date)),
                    'title'=>$specialDay->sd_name
                ];
            });

            $itineraryDateCount = 0;

            foreach ($dates as $date => $details) {

                $validator = Validator::make($details, [
                    'date' => 'required|numeric',
                ]);

                if ($validator->fails()) {
                    throw new WebAPIException("Can not validate some inputs. Please try after refresh your browser.");
                }

                if (!empty($details['types'])) {
                    $emailDates[$date] = [];

                    $formatedDate = new \DateTime($yearMonth.'-'.str_pad($date,2,'0',STR_PAD_LEFT));

                    $emailDates[$date]['date'] = $formatedDate->format('Y-m-d');

                    $workingDay = false;

                    if($formatedDate->format("D")!="Sun"&&$formatedDate->format('D')!='Sat'){
                        $itineraryDateCount++;
                    }

                    $types = DayType::whereIn('dt_id', $details['types'])->get();

                    $emailDates[$date]['dayTypes']=[];

                    foreach ($types as $type) {
                        if ($type->dt_field_work_day) {
                            $workingDay = true;
                        }

                        $emailDates[$date]['dayTypes'][] = [
                            'label'=>$type->dt_code,
                            'value'=> $type->getKey(),
                            'color'=> config('shl.color_codes')[$type->dt_color]
                        ];
                    }

                    $mode="od";

                    if(
                        isset($details['description'])&&
                        isset($details['description']['value'])
                    ){
                        $mode = "si";
                        $emailDates[$date]['description'] = $details['description']['label'];
                    }

                    if(
                        isset($details['joinFieldWorker']) &&
                        isset($details['joinFieldWorker']['jointFieldWorker']) &&
                        isset($details['joinFieldWorker']['jointFieldWorker']['value']) &&
                        isset($details['joinFieldWorker']['bataType'])&&
                        isset($details['joinFieldWorker']['bataType']['value'])
                    ){
                        $mode = "jfw";
                        $emailDates[$date]['description'] = "JFW - ".$details['joinFieldWorker']['jointFieldWorker']['label'];
                        $emailDates[$date]['mileage'] = $details['joinFieldWorker']['mileage'];
                        $emailDates[$date]['bataType'] = $details['joinFieldWorker']['bataType']['label'];
                    }

                    if(
                        isset($details['additionalValues'])&&
                        isset($details['additionalValues']['description'])&&
                        isset($details['additionalValues']['bata'])&&
                        isset($details['additionalValues']['bata']['value'])&&
                        isset($details['additionalValues']['mileage'])
                    ){
                        $mode="arp";
                        $emailDates[$date]['description'] = "Additional Route Plan";
                        $emailDates[$date]['mileage'] = $details['additionalValues']['mileage'];
                        $emailDates[$date]['bataType'] = $details['additionalValues']['bata']['label'];
                    }

                    if(
                        isset($details['changedRoute'])&&
                        isset($details['changedRoute']['description'])&&
                        isset($details['changedRoute']['bata'])&&
                        isset($details['changedRoute']['bata']['value'])&&
                        isset($details['changedRoute']['mileage'])
                    ){
                         $mode="cd";
                         $emailDates[$date]['description'] = "Changed Route By Tab";
                         $emailDates[$date]['mileage'] = $details['changedRoute']['mileage'];
                         $emailDates[$date]['bataType'] = $details['changedRoute']['bata']['label'];
                    };

                    if($mode=="cd"){
                        $details['additionalValues'] = $details['changedRoute'];
                        $mode="arp";
                    }

                    $mileage =null;
                    $bataType = null;

                    if($mode=="jfw"||$mode=='od'){
                        if($mode=="jfw"){
                            $mileage = $details['joinFieldWorker']['mileage'];
                            $bataType = $details['joinFieldWorker']['bataType']['value'];
                        } else if($mode=="od"){
                            $mileage = isset($details['otherDay'])?$details['otherDay']['mileage']:null;
                            $bataType = isset($details['otherDay'])&&isset($details['otherDay']['bataType'])&&isset($details['otherDay']['bataType']['value'])?$details['otherDay']['bataType']['value']:null;
                        }
                    }

                    $itineraryDate = ItineraryDate::create([
                        'i_id' => $itinerary->getKey(),
                        'id_date' => $details['date'],
                        'u_id'=>$mode=='jfw'?$details['joinFieldWorker']['jointFieldWorker']['value']:null,
                        'id_mileage'=>$mileage,
                        'bt_id'=>$bataType,
                        'sid_id' => $mode=="si"?$details['description']['value']:null,
                    ]);

                    foreach ($types as $type) {
                        ItineraryDayType::create([
                            'id_id' => $itineraryDate->getKey(),
                            'dt_id' => $type->getKey(),
                        ]);
                    }

                    if ($workingDay && (!empty($details['description']) || !empty($details['additionalValues']))||!empty($details['joinFieldWorker'])) {

                        if(isset($mode)){

                            if ($mode=="arp") {
                                $addtionalDetails = $details['additionalValues'];
                                $additinalRoutePlan = AdditionalRoutePlan::create([
                                    'arp_description' => $addtionalDetails['description'],
                                    'bt_id' => $addtionalDetails['bata']['value'],
                                    'arp_mileage' => $addtionalDetails['mileage'],
                                    'id_id' => $itineraryDate->getKey(),
                                ]);

                                if (!empty($addtionalDetails['areas'])) {
                                    foreach ($addtionalDetails['areas'] as $area) {
                                        AdditionalRoutePlanArea::create([
                                            'arp_id' => $additinalRoutePlan->getKey(),
                                            'sub_twn_id' => $area['value'],
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    if($mode=="si"){
                        $standrdItineraryDate = StandardItineraryDate::where('sid_id',$details['description']['value'])->with(['bataType','standardItineraryDateAreas','standardItineraryDateAreas.sub_town'])->first();


                        $emailDates[$date]['mileage'] = $standrdItineraryDate->sid_mileage;
                        if($standrdItineraryDate->bataType)
                            $emailDates[$date]['bataType'] = $standrdItineraryDate->bataType->bt_name;

                        $emailDates[$date]['towns'] = [];

                        if($standrdItineraryDate){
                            foreach($standrdItineraryDate->standardItineraryDateAreas as $area){
                                if($area->sub_town){
                                    $emailDates[$date]['towns'][] = $area->sub_town->sub_twn_name;
                                }

                                UserItinerarySubTown::create([
                                    'u_id'=>isset($medicalRep['value'])?$medicalRep['value']:$fieldManager['value'],
                                    'sub_twn_id'=>$area->sub_twn_id,
                                    'arp_id'=>null,
                                    'sid_id'=>$details['description']['value'],
                                    'i_id'=>$itinerary->getKey(),
                                    'id_id'=>$itineraryDate->getKey(),
                                    'uist_year'=>$itinerary->i_year,
                                    'uist_month'=>$itinerary->i_month,
                                    'uist_date'=>$details['date'],
                                    'uist_approved'=>0
                                ]);
                            }
                        }
                    } else if($mode=="jfw"){
                        $jointFieldWorker = User::find($details['joinFieldWorker']['jointFieldWorker']['value']);

                        $areas = $this->getTerritoriesByItinerary($jointFieldWorker,strtotime($itinerary->i_year.'-'.str_pad($itinerary->i_month,2,'0',STR_PAD_LEFT).'-'.str_pad($itineraryDate->id_date,2,'0',STR_PAD_LEFT)),false);

                        foreach($areas as $area){
                            $emailDates[$date]['towns'][] = $area->sub_twn_name;
                            UserItinerarySubTown::create([
                                'u_id'=>isset($medicalRep['value'])?$medicalRep['value']:$fieldManager['value'],
                                'sub_twn_id'=>$area->sub_twn_id,
                                'arp_id'=>null,
                                'sid_id'=>null,
                                'i_id'=>$itinerary->getKey(),
                                'id_id'=>$itineraryDate->getKey(),
                                'uist_year'=>$itinerary->i_year,
                                'uist_month'=>$itinerary->i_month,
                                'uist_date'=>$details['date'],
                                'uist_approved'=>0,
                                'uist_jfw_id'=>$jointFieldWorker->getKey()
                            ]);
                        }
                    } else if($mode=="arp"){
                        foreach($addtionalDetails['areas'] as $area){
                            $emailDates[$date]['towns'][] = $area['label'];
                            UserItinerarySubTown::create([
                                'u_id'=>isset($medicalRep['value'])?$medicalRep['value']:$fieldManager['value'],
                                'sub_twn_id'=>$area['value'],
                                'arp_id'=>$additinalRoutePlan->getKey(),
                                'sid_id'=>null,
                                'i_id'=>$itinerary->getKey(),
                                'id_id'=>$itineraryDate->getKey(),
                                'uist_year'=>$itinerary->i_year,
                                'uist_month'=>$itinerary->i_month,
                                'uist_date'=>$details['date'],
                                'uist_approved'=>0,
                            ]);
                        }
                    }
                }
            }


            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        $emailDates = collect($emailDates)->sortBy('date');


        // Sending Email
        try {
            $emails = EmailSelectedType::where('et_id',2)->with('email')->get();

            Mail::send('emails.itinerary-changed', [
                'dates'=> $emailDates,
                'user'=> $user,
                'loggedUser'=> Auth::user(),
                'month'=> $yearMonth
            ],function(Message $mail) use($emails,$parent, $user) {

                $mail->subject("[Itinerary Changed] ".$user->u_code);

                $mail->to(config('shl.system_email'),"Onefore CRM");

                if($parent&&!Validator::make(['email'=>$parent->email],['email'=>'email'])->fails()){
                    $mail->cc($parent->email,$parent->name);
                }

                foreach ($emails as $email){
                    if($email->email){
                        $mail->cc($email->email->e_email, $email->email->e_name);
                    }
                }
            });

        } catch (\Exception $e){
            // throw $e;
        }


        return [
            'message' => 'Successfully saved the itinerary!',
        ];
    }
    /**
     * Searching a team member
     *
     * @param Request $request
     */
    public function searchTeamMembers(Request $request){
        $validation = Validator::make($request->all(),[
            'where'=>'required|array',
            'where.fm'=>'required|array',
            'where.fm.value'=>'required|numeric|exists:users,id',
        ]);

        $keyword = $request->input('keyword','');

        if($validation->fails())
            throw new WebAPIException($validation->errors()->first());

        $fieldManager = $request->input('where.fm');

        $team = Team::with('teamUsers')->where('fm_id',$fieldManager['value'])->latest()->first();

        if(!$team)
            throw new WebAPIException("Field manager doesn't in any team. He can not get join field workers.");

        $teamUsers = $team->teamUsers;

        $userIds = $teamUsers->pluck('u_id')->all();

        $users = User::whereIn('id',$userIds)->where(function($query)use($keyword){
            $query->orWhere('name','like',"%$keyword%");
            $query->orWhere('user_name','like',"%$keyword%");
            $query->orWhere('u_code','like',"%$keyword%");
        })->get();

        $users->transform(function($user){
            return [
                'label'=>$user->getName(),
                'value'=>$user->getKey()
            ];
        });

        return $users;

    }

    public function searchTeamMembersWithItinerary(Request $request){
        $validation = Validator::make($request->all(),[
            'where'=>'required|array',
            'where.fm'=>'required|exists:users,id',
            'where.date'=>'required|date'
        ]);

        if($validation->fails()){
            return response()->json([]);
        }

        $team = Team::with(['teamUsers','teamUsers.user'])->where('fm_id',$request->input('where.fm'))->latest()->first();

        if(!$team){
            return response()->json([]);
        }

        $users = $team->teamUsers->map(function($teamUser){
            return $teamUser->user;
        });

        $users = $users->filter(function($user){return !!$user;});

        $users->transform(function($user)use($request){
            try{

                $itineraryDate = ItineraryDate::getTodayForUser($user,['additionalRoutePlan'],strtotime($request->input('where.date')),false,false);

                $standardItineraryDateId = $itineraryDate->sid_id?$itineraryDate->sid_id:0;
                $additionalRoutePlanId = 0;
                if(isset($itineraryDate->additionalRoutePlan)){
                    $additionalRoutePlanId = $itineraryDate->additionalRoutePlan->getKey();
                }

                return [
                    "label"=>$user->name,
                    "value"=>$user->getKey(),
                    "standardItinerary"=>$standardItineraryDateId,
                    "additionalRoutePlan"=>$additionalRoutePlanId
                ];
            } catch(\Exception $e){
                return null;
            }
        });


        $users = $users->filter(function($user){return !!$user;});

        $standardItineraryIds = $users->pluck('standardItinerary')->all();
        $routePlanIds = $users->pluck('additionalRoutePlan')->all();

        $this->territoryColumns = [DB::raw('GROUP_CONCAT(DISTINCT st.sub_twn_name) AS names '),'uist.sid_id' ,'uist.arp_id'];
        $townNames = $this->__getSubtownsByItineraryIds($standardItineraryIds,$routePlanIds,[0],['uist.sid_id' ,'uist.arp_id']);

        $users->transform(function($user)use($townNames){
            $names = $townNames->where('sid_id',$user['standardItinerary']);

            if($names->isEmpty()){
                $names = $townNames->where('arp_id',$user['additionalRoutePlan']);
            }

            if($names->isEmpty())
                $names = "ARP";
            else
                $names = $names->first()->names;

            $user['label'] = $user['label'].($names?" - ".$names:"");

            unset($user['standardItinerary']);
            unset($user['additionalRoutePlan']);

            return $user;
        });

        return $users->values();

    }

    public function searchUserByArea(Request $request){

        $ar_id = $request->input('where.ar_id');
        $u_tp_id = $request->input('where.u_tp_id');
        $keyword = $request->input('keyword','');

        $area = Area::where('ar_id',$request->input('where.ar_id'))->first();

        $query = User::query();
        $query->where('u_tp_id',$u_tp_id);

        $loggedUser = Auth::user();
        if($loggedUser->getRoll()==config("shl.area_sales_manager_type")){

            $getAllocatedAreas = $this->getAllocatedTerritories($loggedUser);
            if($getAllocatedAreas){
                $area = Area::whereIn('ar_id',$getAllocatedAreas->pluck('ar_id')->all())->latest()->first();

               $defaultArea = $getAllocatedAreas->pluck('ar_code')->unique();
               $query->where('u_code','LIKE','%'.$defaultArea[0].'%');
            }
        }

        if(isset($ar_id)){
            $query->where('u_code','LIKE','%'.$area->ar_code.'%');
        }

        if(isset($keyword)){
            $query->where('name','LIKE','%'.$keyword.'%');
        }

        $results = $query->get();

        $results->transform(function($user){
            return[
                'value' => $user->id,
                'label' => $user->name
            ];
        });
        return $results;
    }
}
