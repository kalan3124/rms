<?php
namespace App\Http\Controllers\Web\Reports;

use App\Models\BataType;
use App\Models\Reason;
use Illuminate\Http\Request;
use App\Models\Team;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\Expenses;
use \DateTime;
use DateInterval;
use DatePeriod;
use App\Models\ItineraryDate;
use App\Models\VehicleTypeRate;
use App\Models\Itinerary;
use Illuminate\Support\Facades\Auth;
use App\Models\BataCategory;
use App\Models\TeamUser;
use App\Models\User;
use App\Models\UserAttendance;
use App\Models\StationMileage;
use App\Models\UserTeam;

class ExpenceStatementSummeryReportController extends ReportController{
    protected $title = "Expenses Statement Summary Report";

    protected $defaultSortColumn="tm_name";

    public function search(Request $request){
        $validation = Validator::make($request->all(),[
            'values'=>'required|array',
            'values.s_date'=>'required|date',
            'values.e_date'=>'required|date'
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $values = $request->input('values');

        $start    = (new \DateTime($values['s_date']));
        $end      = (new \DateTime($values['e_date']));

        $whereEnd = strtotime($values['e_date'])<time()? $values['e_date']:date('Y-m-d');

        $user = Auth::user();

        $result = Team::query();
        $result->where('tm_id','!=',2);

        $result->with(['teamUsers','teamUsers.user','user']);
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $users = User::getByUser($user);

            $teamUsers = TeamUser::whereIn('u_id',$users->pluck('id')->all())->get();

            $result->where('tm_id',$teamUsers->pluck('tm_id')->all());
        }


        $teams = UserTeam::where('u_id',$user->getKey())->get();
        if($teams->count()){
            $result->whereIn('tm_id',$teams->pluck('tm_id')->all());
        } 

        if($request->input('values.user.value')){
            $teamUser = TeamUser::where('u_id',$request->input('values.user.value'))->latest()->first();
            if($teamUser&&$teamUser->team){
                $result->where('tm_id',$teamUser->tm_id);
            }
        }else if($request->input('values.team_id.value')){
            $result->where('tm_id',$request->input('values.team_id.value'));
        }

        $teams = $result->get();

        $results = [];

        $bataTypes = BataType::latest()->get();
        $bataCatTypes = BataCategory::get();
        $reasons = Reason::latest()->where('rsn_type',config('shl.expenses_reason_type'))->where('rsn_id','!=',14)->get();


        foreach ($teams as $teamKey => $team) {

            $teamCountRow=[
                'tm_id' =>[
                    'value'=>$team->getKey(),
                    'label'=>""
                ],
                'u_code'=>null,
                'name'=>null,
                'special'=>1,
                'grnd_tot'=>0,
                "tm_id_rowspan"=>0,
                'btc_tot'=>0,
                'mileage'=>0,
                'pay_mileage'=>0,
                'pvt_mileage'=>0,
                'ad_mileage'=>0,
                'mileage_tot'=>0
            ];

            foreach($bataCatTypes as $bataCatType){
                $teamCountRow['btc_id_'.$bataCatType->getKey()] = 0;
            }

            foreach ($bataTypes as $bataType) {
                if(isset($bataType->btc_id))
                    $teamCountRow['bt_value_'.$bataType->getKey()] = 0;
            }

            foreach ($reasons as $key => $reason) {
                $teamCountRow['exp_value_'.$reason->getKey()] = 0;
            }

            $users = $team->teamUsers->map(function($teamUser){

                return $teamUser->user;
            });

            if($team->user){
                $users->push($team->user);
            }

            $users = $users->filter(function($user)use($request){

                $validated = true;

                if(!$user)
                    $validated = false;

                if($validated&&$request->input('values.divi_id')){
                    $validated = $user->divi_id==$request->input('values.divi_id.value');
                }

                if($validated&&$request->input('values.user')){
                    $validated =   $user->id==$request->input('values.user.value');
                }

                if(!$validated)
                    return false;

                return true;
            });

            $base_tot = 0;
            foreach ($users->values() as $userKey => $user) {

                $grandTotal=0;

                $row = [];
                if($userKey==0){
                    $row['tm_id'] = [
                        'value'=>$team->getKey(),
                        'label'=>$team->tm_name
                    ];
                    $row['tm_id_rowspan']=$users->count()+1;
                } else {
                    $row['tm_id'] = null;
                    $row['tm_id_rowspan'] = 0;
                }


                $row['u_code_style']=[
                    'background'=>'#D3D3D3',
                    'border'=>'1px solid #fff'
                ];
                $row['name_style']=[
                    'background'=>'#D3D3D3',
                    'border'=>'1px solid #fff'
                ];
                $row['grnd_tot_style']=[
                    'background'=>'#D3D3D3',
                    'border'=>'1px solid #fff'
                ];
                $row['tm_id_style']=[
                    'background'=>'#D3D3D3',
                    'border'=>'1px solid #fff'
                ];
                $row['u_code'] = $user->u_code;
                $row['name']=$user->name;

                $itineraries = [];

                $interval = DateInterval::createFromDateString('1 month');
                $period   = new DatePeriod($start, $interval, $end);

                foreach ($period as $dt) {
                    $year =  $dt->format("Y");
                    $month = $dt->format('m');

                    $itineraries[] = Itinerary::where(function($query)use($user){
                        $query->orWhere('rep_id',$user->id?$user->id:0);
                        $query->orWhere('fm_id',$user->id?$user->id:0);
                    })
                    ->whereNotNull('i_aprvd_at')
                    ->where('i_year',$year)
                    ->where('i_month',$month)
                    ->latest()
                    ->first();
                }

                $interval = DateInterval::createFromDateString('1 day');
                $period   = new DatePeriod($start, $interval, $end);


                $itineraries = collect($itineraries);

                $itineraryRelations = [
                    'joinFieldWorker',
                    'itineraryDayTypes',
                    'itineraryDayTypes.dayType',
                    'standardItineraryDate',
                    'standardItineraryDate.bataType',
                    'additionalRoutePlan',
                    'additionalRoutePlan.bataType',
                    'changedItineraryDate',
                    'changedItineraryDate.bataType',
                    'bataType'
                ];

                $itineraryDates = ItineraryDate::with($itineraryRelations)
                ->whereIn('i_id',$itineraries->pluck('i_id')->all())
                ->get();

                $itineraryDates->transform(function( ItineraryDate $itineraryDate)use($itineraries,$user,$values){
                    $mileage=0;
                    $bataValue = 0;
                    $bataTypeId =0;

                    $itinerary = $itineraries->where('i_id',$itineraryDate->i_id)->first() ;

                    $date = $itinerary->i_year."-".str_pad($itinerary->i_month,2,"0",STR_PAD_LEFT).'-'.str_pad($itineraryDate->id_date,2,"0",STR_PAD_LEFT);

                    $vehicleTypeRateInst = VehicleTypeRate::where('vht_id',$user->vht_id)->whereDate('vhtr_srt_date','<=',$date)->where('u_tp_id',$user->u_tp_id)->latest()->first();
                    $vehicleTypeRate = $vehicleTypeRateInst?$vehicleTypeRateInst->vhtr_rate:0;

                    $attendanceStatus = UserAttendance::where('u_id','=',$user->id)->whereDate('check_in_time','=',$date)->whereNotNull('check_out_time')->latest()->first();

                    $stationMileage = StationMileage::where('u_id',$user->id)->whereDate('exp_date',$date)->first();

                    $backDatedExpences = Expenses::where('u_id',$user->id)->whereDate('exp_date',$date)->first();

                    $details = $itineraryDate->getFormatedDetails();
                    $bataType = $details->getBataType();
                    $bataValue = $bataType?$bataType->bt_value:0;
                    $bataTypeId = $bataType?$bataType->bt_id:0;
                    $mileage = $details->getMileage()*$vehicleTypeRate;

                    if(strtotime($values['s_date'])<=strtotime($date)&&strtotime($values['e_date'])>=strtotime($date)&&strtotime($date)<=time()&&( $attendanceStatus||$backDatedExpences||$stationMileage||(!$details->getFieldWorkingDay() &&$details->getWorkingDay()))){
                        return [
                            "mileageValue"=>$mileage,
                            'mileage'=>$details->getMileage(),
                            "bataValue"=>$bataValue,
                            "date"=>$date,
                            'bataTypeId'=>$bataTypeId,
                            'bataTypeCat'=>$bataType?$bataType->btc_id:0,
                            'vehicleTypeRate'=>$vehicleTypeRate,
                            'type'=>$details->getDateType()
                        ];
                    }

                    return null;
                });

                $itineraryDates = $itineraryDates->filter(function($itineraryDate){
                    return !!$itineraryDate;
                })->values();

                // Getting vehicle type rate for the last date.
                // We dont want to get vehicle type rate by another query
                // Vehicle type rate is fetched in $itineraryDates
                $lastDate = $itineraryDates->last();
                $vehicleTypeRate = $lastDate?$lastDate['vehicleTypeRate']:0;


                // Bata values
                $bataTotal = 0;

                foreach ($bataTypes as $bataType) {
                    if(isset($bataType->btc_id)){
                        $value = $itineraryDates->where('bataTypeId',$bataType->getKey())->sum('bataValue');

                        if(!isset($row['btc_id_'.$bataType->btc_id]))
                            $row['btc_id_'.$bataType->btc_id] =$value;
                        else
                            $row['btc_id_'.$bataType->btc_id] += $value;

                        $bataTotal += $value;

                        $teamCountRow['btc_id_'.$bataType->btc_id] += $value;

                    }
                }

                $row['btc_tot'] = $bataTotal;
                $teamCountRow['btc_tot'] += $bataTotal;

                $grandTotal += $row['btc_tot'];

                // base allowance
                $row['base_lov'] = $user->base_allowances;
                $base_tot += $user->base_allowances;
                $teamCountRow['base_lov'] = $base_tot;

                $grandTotal += $row['base_lov'];
                // Mileage (Rs)
                $mileageTotal = 0;
                $mileageValue = $itineraryDates->sum('mileageValue');
                $mileage = $itineraryDates->sum('mileage');

                $additionalMileage = Expenses::whereDate('exp_date',">=",$start->format("Y-m-d"))
                    ->whereDate('exp_date','<=',$whereEnd)
                    ->where('u_id',$user->id)
                    ->where('rsn_id','=',14)
                    ->sum('exp_amt')*$vehicleTypeRate;

                $row['mileage']= $mileage;

                $row['pay_mileage'] = $mileageValue;
                $row['pvt_mileage'] = ($user->u_pvt_mileage_limit?$user->u_pvt_mileage_limit:0)*$vehicleTypeRate;
                $row['ad_mileage'] = $additionalMileage;

                $mileageTotal = $row['pay_mileage']+$row['pvt_mileage']+$row['ad_mileage'];
                $row['mileage_tot'] = $mileageTotal;

                $teamCountRow['mileage'] += $row['mileage'];
                $teamCountRow['pay_mileage'] += $row['pay_mileage'];
                $teamCountRow['pvt_mileage'] += $row['pvt_mileage'];
                $teamCountRow['ad_mileage'] += $row['ad_mileage'];
                $teamCountRow['mileage_tot'] += $row['mileage_tot'];

                $grandTotal += $mileageTotal;


                // Other expenses
                foreach ($reasons as $key => $reason) {
                    $value = Expenses::where('rsn_id',$reason->getKey())
                        ->whereDate('exp_date',">=",$start->format("Y-m-d"))
                        ->whereDate('exp_date','<=',$whereEnd)
                        ->where('u_id',$user->id)
                        ->where('rsn_id','!=',14)
                        ->sum('exp_amt');
                    $row['exp_value_'.$reason->getKey()] = $value;
                    $teamCountRow['exp_value_'.$reason->getKey()] += $value;
                    $grandTotal+=$value;
                }

                $row['grnd_tot'] = $grandTotal;
                $teamCountRow['grnd_tot'] += $grandTotal;

                $results[] = $row;
            }

            if($users->count())
                $results[]=$teamCountRow;
        }

        $sampleRow = $results[0];
        $results = collect($results);
        $sampleRow['tm_id']="";
        unset($sampleRow['tm_id_rowspan']);
        $sampleRow['u_code']="Grand Total";
        $sampleRow['name']="";
        $sampleRow['special']=1;
        foreach ($sampleRow as $key => $value) {
            if(!in_array($key,['tm_id','u_code','name','special','tm_id_style','u_code_style','name_style','grnd_tot_style']))
                $sampleRow[$key] = $results->where('special',1)->sum($key);
        }

        $results->push($sampleRow);

        return [
            'count'=>0,
            'results'=>$results
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
        // $bataCount = BataType::count();
        $bataCatCount = BataCategory::count();
        $expencesCount = Reason::latest()->where('rsn_type',config('shl.expenses_reason_type'))->where('rsn_id','!=',14)->count();

        $columns = [[
            [
                "title"=>"",
                "colSpan"=>3
            ],
            [
                "title"=>"Bata (Rs)",
                "colSpan"=>$bataCatCount+2
            ],
            [
                "title"=>"Mileage pay (Km)",
                "colSpan"=>5
            ],
            [
                "title"=>"Other Types",
                "colSpan"=>$expencesCount
            ],
            [
                "title"=>""
            ]
        ]];

        return $columns;
    }

    protected function setColumns($columnController, Request $request){
        $columnController->ajax_dropdown('tm_id')->setLabel("Team")->setSearchable(false);
        $columnController->number('u_code')->setLabel("ID")->setSearchable(false);
        $columnController->number("name")->setLabel("Agent")->setSearchable(false);

        $bataCatTypes = BataCategory::get();
        foreach ($bataCatTypes as $key => $bataCatType) {
            $columnController->number('btc_id_'.$bataCatType->getKey())->setLabel($bataCatType->btc_category)->setSearchable(false);
        }
        $columnController->number('base_lov')->setLabel('Base allowances');
        $columnController->number('btc_tot')->setLabel('Bata Total');


        $columnController->number('mileage')->setLabel(" Mileage")->setSearchable(false);
        $columnController->number('pay_mileage')->setLabel("Mileage Pay")->setSearchable(false);
        $columnController->number('ad_mileage')->setLabel("Additional Mileage")->setSearchable(false);
        $columnController->number('pvt_mileage')->setLabel("Private Mileage")->setSearchable(false);
        $columnController->number('mileage_tot')->setLabel("Mileage Total")->setSearchable(false);

        $reasons = Reason::latest()->where('rsn_type',config('shl.expenses_reason_type'))->where('rsn_id','!=',14)->get();
        foreach ($reasons as $key => $reason) {
            $columnController->number('exp_value_'.$reason->getKey())->setLabel($reason->rsn_name)->setSearchable(false);
        }
        $columnController->number('grnd_tot')->setLabel("Grand Total")->setSearchable(false);
    }

    protected function setInputs($inputController){
        $inputController->ajax_dropdown("team_id")->setWhere(['divi_id'=>'{divi_id}'])->setLabel("Team")->setLink("team")->setValidations('');
        $inputController->ajax_dropdown('user')->setWhere(["tm_id" => "{team_id}",'u_tp_id'=> '3|2'.'|'.config('shl.product_specialist_type'),'divi_id'=>'{divi_id}'])->setLabel('PS/MR or FM')->setLink('user')->setValidations('');
        $inputController->ajax_dropdown("divi_id")->setLabel("Division")->setLink("division")->setValidations('');
        $inputController->date("s_date")->setLabel("From")->setValidations('');
        $inputController->date("e_date")->setLabel("To")->setValidations('');
        $inputController->setStructure([["team_id","divi_id","user"],["s_date","e_date"]]);
    }
}
