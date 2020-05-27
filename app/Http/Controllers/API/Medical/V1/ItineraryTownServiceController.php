<?php
namespace App\Http\Controllers\API\Medical\V1;

use App\Models\Itinerary;
use App\Traits\Territory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\SpecialDay;
use App\Models\UserAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ItineraryDateChange;
use App\Models\ItineraryDateChangeArea;
use App\Models\Notification;
use App\Models\SubTown;
use App\Models\BataType;
use App\Models\TeamUser;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\MediAPIException;
use App\Exceptions\WebAPIException;
use App\Models\ItineraryDate;
use App\Models\UserItinerarySubTown;
use App\Models\Expenses;
use App\Models\StationMileage;

class ItineraryTownServiceController extends Controller {

     use Territory;

     protected function checkItineraryHasChanged($user,$timestamp){
        // Checking the weather itinerary details are modified or not
        if($timestamp){
            $itinerary = Itinerary::where('i_year',date('Y'))
                ->where('i_month',date('m'))
                ->where(function($query) use($user) {
                    $query->orWhere('rep_id',$user->getKey());
                    $query->orWhere('fm_id',$user->getKey());
                })
                ->whereNotNull('i_aprvd_at')
                ->latest()
                ->first();

            $changedItinerary = ItineraryDateChange::where('u_id',$user->getKey())
                ->whereNotNull('idc_aprvd_u_id')
                ->whereDate('idc_date',date('Y-m-d'))
                ->latest()
                ->first();
                
            if(($itinerary&&strtotime($itinerary->updated_at)<$timestamp/1000)&&($changedItinerary&&strtotime($changedItinerary->idc_aprvd_at)<$timestamp/1000)){
                throw new MediAPIException("You have no latest data.",38);
            }
        }

    }

     public function index(Request $request){
           // Filtering user inputs
        $user = Auth::user();

        // Seperate the year and month
        $year = date('Y');
        $month = date('m');  
        
        $this->checkItineraryHasChanged($user,$request->input('timestamp'));

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

        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type')
        ])){
            $query->where('rep_id',$user->getKey());
        } else {
             $query->where('fm_id',$user->getKey());
        }

        $itinerary = $query->first();

        // Aborting if itinerary not found
        if (!$itinerary) {
            return response()->json([
                'result'=>false
            ]);
        }

        $standardItineraryIds = $itinerary->itineraryDates->pluck('standardItineraryDate.sid_id')->all();
        $additionalRoutePlanIds = $itinerary->itineraryDates->pluck('additionalRoutePlan.arp_id')->all();
        $changedPlanIds = $itinerary->itineraryDates->pluck('changedItineraryDate.idc_id')->all();

        $rows = $this->__getSubtownsByItineraryIds($standardItineraryIds,$additionalRoutePlanIds,$changedPlanIds,['uist.sid_id','uist.arp_id','uist.idc_id','st.sub_twn_id']);

        $dates = $itinerary->itineraryDates->map(function($itineraryDate)use($rows,$year,$month){
            $towns = collect([]);
            $dateType = 2;
            $info = "";
            $additionalInfo = "";
            $mileage = 0.00;
            $bataType = null;

            if(isset($itineraryDate->changedItineraryDate)&&isset($itineraryDate->changedItineraryDate->idc_aprvd_at)){

                $towns = $rows->where('idc_id',$itineraryDate->idc_id);
                $info = "Changed Itinerary";
                $dateType = 7;
                $additionalInfo = "";
                $mileage = $itineraryDate->changedItineraryDate->idc_mileage;
                $bataType = isset($itineraryDate->changedItineraryDate->bataType)?$itineraryDate->changedItineraryDate->bataType:$bataType;

            }else if(isset($itineraryDate->standardItineraryDate)){
                $towns = $rows->where('sid_id',$itineraryDate->sid_id);
                $info = "Standard Itinerary";
                $dateType = 3;
                $additionalInfo = $itineraryDate->standardItineraryDate->sid_description;
                $mileage = $itineraryDate->standardItineraryDate->sid_mileage;
                $bataType = isset($itineraryDate->standardItineraryDate->bataType)?$itineraryDate->standardItineraryDate->bataType:$bataType;

            }else if(isset($itineraryDate->additionalRoutePlan)){
                $towns = $rows->where('arp_id',$itineraryDate->additionalRoutePlan->arp_id);
                $info = "Additional Route Plan";
                $dateType=4;
                $additionalInfo = $itineraryDate->additionalRoutePlan->arp_description;
                $mileage = $itineraryDate->additionalRoutePlan->arp_mileage;
                $bataType = isset($itineraryDate->additionalRoutePlan->bataType)?$itineraryDate->additionalRoutePlan->bataType:$bataType;

            } else if(isset($itineraryDate->joinFieldWorker)){
                $dateType = 5;
                $info = "Joint field worker";
                $additionalInfo = $itineraryDate->joinFieldWorker->name;
                $towns = $this->getTerritoriesByItinerary($itineraryDate->joinFieldWorker,strtotime( $year.'-'.str_pad($month,STR_PAD_LEFT).'-'.str_pad($itineraryDate->id_date,STR_PAD_LEFT) ));
                $mileage = $itineraryDate->id_mileage;
                $bataType = isset($itineraryDate->bataType)?$itineraryDate->bataType:$bataType;
            } else {
                $info = "You don't have a route";
                $mileage = $itineraryDate->id_mileage;
                $bataType = isset($itineraryDate->bataType)?$itineraryDate->bataType:$bataType;
                $additionalInfo = date('l',strtotime(date('Y-m-'.str_pad($itineraryDate->id_date,2,'0',STR_PAD_LEFT))));
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
                'types'=>$dayTypes->values(),
                "info"=>$info,
                "dateType"=>$dateType,
                "towns"=>$towns->values(),
                "mileage"=>$mileage,
                "bataType"=>$bataType,
                "additionalInfo"=>$additionalInfo
            ];
            
        });

        $specialDays = SpecialDay::whereYear('sd_date',$year)->whereMonth('sd_date',$month)->get();

        $specialDays->transform(function($specialDay){
            return [
                'date'=>date('d',strtotime($specialDay->sd_date)),
                'info'=>"Holiday",
                "additionalInfo"=>$specialDay->sd_name,
                "towns"=>[],
                "types"=>[],
                "dateType"=>1,
                "mileage"=>0.00,
                "bataType"=>[
                    "id"=>0,
                    "name"=>""
                ]
            ];
        });

        $begin = new \DateTime(date('Y-m-01'));
        $end = new \DateTime(date('Y-m-t'));

        $interval = \DateInterval::createFromDateString('1 day');

        $retArr = [];

        $attendance = UserAttendance::whereBetween('check_in_time', [$begin, $end])
            ->where('u_id',$user->getKey())
            ->select([DB::raw('DATE(MIN(check_in_time)) AS check_in_time'),DB::raw('DATE(MAX(check_out_time)) AS check_out_time')])
            ->groupBy(DB::raw('DATE(check_in_time)'))
            ->get();

        $attendance->transform(function($att){
            return [
                'check_in_time' => $att->check_in_time?date('Y-m-d',strtotime($att->check_in_time)):NULL,
                'check_out_time' => $att->check_out_time?date('Y-m-d',strtotime($att->check_out_time)):NULL
            ];
        });

        $staionMileages = DB::table('station_mileage')
            ->select( DB::raw( 'COUNT(*) AS cnt , DATE( exp_date) AS date'))
            ->where('u_id',$user->getKey())
            ->whereMonth('exp_date',$month)
            ->whereYear('exp_date',$year)
            ->groupBy(DB::raw('DATE(exp_date)'))
            ->get();

        for($dt = $begin; $dt <= $end; $dt->modify('+1 day')){
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
                    'info'=>"Not planned",
                    "additionalInfo"=>$dt->format("l"),
                    "towns"=>[],
                    "types"=>[],
                    "dateType"=>6,
                    "mileage"=>0.00,
                    "bataType"=>[
                        "id"=>0,
                        "name"=>""
                    ]
                ];
            }

            $attendanceType = 0;

            if(isset($attendanceForDay)){
                $attendanceType=1;

                if(isset($attendanceForDay['check_out_time']))
                    $attendanceType=2;
            }

            $day['attendanceType'] = $attendanceType;

            $stationAdded = false;
            $staionMileage = $staionMileages->where('date',$dt->format('Y-m-d'))->first();

            if(isset($staionMileage)&&$staionMileage->cnt)
                $stationAdded = true;

            $day['stationAdded'] = $stationAdded;

            $retArr[] = $day;
        }

        return [
            "result"=>true,
            "date"=>$retArr,
            'approved'=>!!$itinerary->i_aprvd_at
        ];
    }

    public function changeItineraryByMr(Request $request){

        $user = Auth::user();
        
        $attendance = UserAttendance::where('u_id',$user->getKey())->whereDate('check_in_time',date('Y-m-d'))->count();

        if($attendance){
            $expenses = Expenses::where('u_id',$user->getKey())->where('exp_date',date('Y-m-d'))->count();
            $stationMileage = StationMileage::where('u_id',$user->getKey())->where('exp_date',date('Y-m-d'))->count();

            if($expenses||$stationMileage){
                throw new MediAPIException("Station mileage or expenses has already exists for the day.",33);
            }
        }

        $itineraryDateChange = ItineraryDateChange::create([
            'u_id'=>$user->getKey(),
            'idc_date'=>date('Y-m-d'),
            'idc_mileage'=>$request->input('mileage'),
            'bt_id'=>$request->input('bataId'),
            'remark'=>$request->input('remark'),
            'description'=>$request->input('description')
        ]);

        $bataName = "Not Set";
        $bataType = BataType::find( $request->input('bataId'));
        if(isset($bataType)){
            $bataName = $bataType->bt_name;
        }

        $subTowns = json_decode($request->input('subTowns'),true);
        
        $subTownNames = [];

        foreach ($subTowns as $subTown) {
            ItineraryDateChangeArea::create([
                'idc_id'=>$itineraryDateChange->getKey(),
                'sub_twn_id'=>$subTown['sub_twn_id']
            ]);

            $subTown = SubTown::find($subTown['sub_twn_id']);
            if(isset($subTown)){
                $subTownNames[] = $subTown->sub_twn_name;
            }
        }

        $subTownNames = implode(", ",$subTownNames);

        $teamUser = TeamUser::with('team')->where('u_id',$user->getKey())->latest()->first();

        $fieldManager = ($teamUser&&$teamUser->team)? $teamUser->team->fm_id:0;

        if($fieldManager){
            Notification::create([
                'n_title'=>$user->name." is requesting for change the itinerary on ".date('Y-m-d'),
                'n_content'=>"<b>".$user->name.
                    "</b> is requested to change the itinerary on <b>".date("Y-m-d").
                    "</b>.<br/> Bata Type:- <b>".$bataName.
                    "</b>.<br/> Mileage:- <b>".$request->input('mileage').
                    "</b>.<br/> Sub Towns:- <b>".$subTownNames.
                    "</b>.<br/> Time:- <b>".time("H:i:s")."</b>",
                'u_id'=>$fieldManager,
                'n_created_u_id'=>$user->getKey(),
                'n_type'=>1,
                'n_ref_id'=>$itineraryDateChange->getKey()
            ]);
        }

        return [
            "result"=>true,
            'message'=>"Successfully submited your change. Your changes will apply after FM confirmation."
        ];
    }

    public function approveItinerary(Request $request){
        $validation = Validator::make($request->all(),[
            'id'=>'required|numeric|exists:itinerary_date_changes,idc_id'
        ]);

        if($validation->fails()){
            throw new MediAPIException("Invalid request. Please try after refresh your browser.");
        }

        $user = Auth::user();

        try{

            DB::beginTransaction();

            $itineraryChangedDate = ItineraryDateChange::with(['areas','user'])->where('idc_id',$request->input('id'))->first();

            if($itineraryChangedDate->idc_aprvd_u_id)
                throw new MediAPIException("Already approved your itinerary date!");

            $itineraryChangedDate->update([
                'idc_aprvd_u_id'=>$user->getKey(),
                'idc_aprvd_at'=>date("Y-m-d H:i:s")
            ]);

            Notification::whereDate('created_at',$itineraryChangedDate->created_at->format('Y-m-d'))
                ->where('u_id',$user->getKey())
                ->where('n_type',1)
                ->where('n_seen',0)
                ->where('n_ref_id',$itineraryChangedDate->getKey())
                ->update([
                    'n_seen'=>1,
                    'created_at'=>date('Y-m-d H:i:s')
                ]);
    
            $timestamp = strtotime($itineraryChangedDate->idc_date);
    
            $itineraryDate = ItineraryDate::getTodayForUser($itineraryChangedDate->user,[],$timestamp,true);
            $itineraryDate->idc_id = $itineraryChangedDate->getKey();
            $itineraryDate->update();

            UserItinerarySubTown::where('id_id',$itineraryDate->getKey())->delete();

            $areas = [];
            foreach ($itineraryChangedDate->areas as $area) {
                $areas[] = [
                    "u_id"=>$itineraryChangedDate->u_id,
                    "sub_twn_id"=>$area->sub_twn_id,
                    "i_id"=>$itineraryDate->i_id,
                    "id_id"=>$itineraryDate->id_id,
                    "uist_year"=>date('Y',$timestamp),
                    "uist_month"=>date('m',$timestamp),
                    "uist_date"=>date('d',$timestamp),
                    "uist_approved"=>1,
                    'idc_id'=>$itineraryChangedDate->idc_id
                ];
            }
    
            UserItinerarySubTown::insert($areas);

            Notification::create([
                'n_title'=>"Your itinerary has been approved.",
                'n_content'=>"The itinerary that you changed on <b>".$itineraryChangedDate->idc_date."</b> at <b>".$itineraryChangedDate->created_at->format('H:i:s')."</b> is approved by <b>".$user->name."</b> from <b>".date('Y-m-d H:i:s')."</b>. Please refresh your app to take effect.",
                'u_id'=>$itineraryChangedDate->u_id,
                'n_created_u_id'=>$user->getKey(),
                'n_type'=>2
            ]);

            DB::commit();
    
        } catch(\Exception $e) {
            DB::rollBack();

            throw $e;
        }

        return response()->json([
            'message'=>"Successfully approved your itinerary date and sent a notification to MR/PS.",
            "result"=>true
        ]);
        
    }
}
?>