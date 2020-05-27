<?php
namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamUser;
use App\Models\User;
use App\Models\ItineraryDate;
use App\Models\Team;
use App\Exceptions\WebAPIException;
use App\Models\Itinerary;
use \DateTime;
use DateInterval;
use DatePeriod;
use App\Models\BataType;
use App\Models\VehicleTypeRate;
use App\Models\Expenses;
use App\Models\Reason;
use App\Models\BataCategory;
use App\Models\GPSTracking;
use App\Models\ProductiveVisit;
use App\Models\UserAttendance;
use App\Models\SpecialDay;
use App\Models\StationMileage;
use Illuminate\Support\Facades\DB;
use App\Models\SubTown;
use App\Models\UnproductiveVisit;
use Illuminate\Database\Eloquent\Builder;

class ExpenceStatementReportController extends ReportController{

    protected $title = "Expense Statement Report";

    public function __searchBy($values){

        if(!isset($values['user'])||!isset($values['user']['value']))
            throw new WebAPIException("User field is required!");

        $userId = $values['user']['value'];

        $user = User::find($userId);

        if(!isset($values['s_date'])||!isset($values['e_date']))
            throw new WebAPIException("From date and to date is required!");

        $start = new \DateTime($values['s_date']);
        $end = new \DateTime($values['e_date']);
        $end = $end->modify('1 day');

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval ,$end);

        $whereEnd = strtotime($values['e_date'])<time()? $values['e_date']:date('Y-m-d');

        $itineraries = [];

        if(date('m',strtotime($values['s_date'])) != date('m')){
            $table_name = 'gps_tracking_'.date('Y',strtotime($values['s_date'])).'_'.date('m',strtotime($values['s_date']));
        } else {
            $table_name = 'gps_tracking';
        }

        $fromDate = date('Y-m-d 00:00:00',strtotime($values['s_date']));
        $toDate = date('Y-m-d 23:59:59',strtotime($values['e_date']));

        $months = $this->getMonthsBetween($fromDate,$toDate);

        // return $months;

        foreach ($period as $dt) {
            $year =  $dt->format("Y");
            $month = $dt->format('m');

            $itineraries[] = Itinerary::where(function($query)use($user){
                $query->orWhere('rep_id',$user->getKey());
                $query->orWhere('fm_id',$user->getKey());
            })
            ->whereNotNull('i_aprvd_at')
            ->where('i_year',$year)
            ->where('i_month',$month)
            ->latest()
            ->first();

        }

        $itineraries = collect($itineraries);

        $itineraryRelations = [
            'joinFieldWorker',
            'itineraryDayTypes',
            'itineraryDayTypes.dayType',
            'standardItineraryDate',
            'standardItineraryDate.bataType',
            'standardItineraryDate.bataType.bataCategory',
            'standardItineraryDate.areas',
            'standardItineraryDate.areas.subTown',
            'additionalRoutePlan',
            'additionalRoutePlan.bataType',
            'additionalRoutePlan.bataType.bataCategory',
            'additionalRoutePlan.areas',
            'additionalRoutePlan.areas.subTown',
            'changedItineraryDate',
            'changedItineraryDate.bataType',
            'changedItineraryDate.bataType.bataCategory',
            'changedItineraryDate.areas',
            'changedItineraryDate.areas.subTown',
            'bataType',
            'bataType.bataCategory',
            'itinerary'
        ];

        $itineraryDates = ItineraryDate::with($itineraryRelations)
        ->whereIn('i_id',$itineraries->pluck('i_id')->all())
        ->get();


        $itineraryDates->transform(function( ItineraryDate $itineraryDate)use($itineraries,$itineraryRelations,$user,$months){

            $formatedDate = $itineraryDate->getFormatedDetails();

            $mileage= $formatedDate->getMileage();
            $bataType = $formatedDate->getBataType();
            $towns = $formatedDate->getSubTowns();
            $types = $formatedDate->getDayTypes();

            // Generating the date
            $itinerary = $itineraries->where('i_id',$itineraryDate->i_id)->first();
            $date = $itinerary->i_year."-".str_pad($itinerary->i_month,2,"0",STR_PAD_LEFT).'-'.str_pad($itineraryDate->id_date,2,"0",STR_PAD_LEFT);

            // Vehicle type rate retrieving
            $vehicleTypeRateInst = VehicleTypeRate::where('vht_id',$user->vht_id)->whereDate('vhtr_srt_date','<=',$date)->where('u_tp_id',$user->u_tp_id)->latest()->first();
            $vehicleTypeRate = $vehicleTypeRateInst?$vehicleTypeRateInst->vhtr_rate:0;


            // If join field worker selected areas picking by his itinerary
            if(isset($itineraryDate->joinFieldWorker)){
                $jfwItineraryDate = ItineraryDate::getTodayForUser($itineraryDate->joinFieldWorker,$itineraryRelations,strtotime($date));
                $jfwFormatedDate = $jfwItineraryDate->getFormatedDetails();
                $towns = $jfwFormatedDate->getSubTowns();
            }

            // Formating town names
            $townNames = $towns->map(function(SubTown $subTown){
                return $subTown->sub_twn_name;
            })->all();
            $townNames = implode(', ',$townNames);

            $typeNames = implode(', ',array_map(function($dayType){
                return $dayType->dt_name;
            },$types));


            return [
                "mileage"=>$mileage,
                "date"=>$date,
                'townNames'=>$townNames,
                'bataType'=>$bataType,
                'itineraryId'=>$itineraryDate->i_id,
                'rate'=>$vehicleTypeRate,
                'dateType'=>$formatedDate->getDateType(),
                'isWorking'=>$formatedDate->getWorkingDay(),
                'isFieldWorking'=>$formatedDate->getFieldWorkingDay(),
                'typeNames'=>$typeNames
            ];
        });

        $expences = Expenses::where('u_id',$userId)->whereDate('created_at','>=',$start->format("Y-m-d"))->whereDate('created_at','<=',$whereEnd)->get();

        $expences->transform(function($expence){
            $expence->date = date('Y-m-d',\strtotime($expence->exp_date));

            return $expence;
        });

        $bataCategories = BataCategory::all();

        $expencesTypes = Reason::where('rsn_type',config('shl.expenses_reason_type'))->get();

        $rows = [];
        $newRows = [];

        // Looping over dates
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start, $interval, $end);

        // return [$period];
        $sum_of_addtional = 0;
        $sum_of_private = 0;
        foreach ($period as $dt) {

            // New Gps Calculations Start

            $fromDateTime = date('Y-m-d 00:00:00',strtotime($dt->format('Y-m-d')));
            $toDateTime = date('Y-m-d 23:59:59',strtotime($dt->format('Y-m-d')));

            if(count($months)==1&&$months[0]==date('Y_m')){
                $coordinates = GPSTracking::where('u_id',$userId)->whereBetween('gt_time',[$fromDateTime,$toDateTime])->get();
                
            } else {
                $coordinates = collect([]);

                $coordArray = [];

                if(end($months)==date('Y_m')){
                    $coordArray[] = GPSTracking::where('u_id',$userId)->whereBetween('gt_time',[$fromDateTime,$toDateTime])->get();
                    array_pop($months);
                }

                foreach($months as $month){
                    $coordArray[] = DB::table('gps_tracking_'.$month)->where('u_id',$userId)->whereBetween('gt_time',[$fromDateTime,$toDateTime])->get();
                }

                foreach($coordArray as $coordinateBatch){
                    $coordinates = $coordinates->merge($coordinateBatch);
                }
            }

            $coordinates->transform(function($coordinate){

                return [
                    'lng'=>$coordinate->gt_lon,
                    'lat'=>$coordinate->gt_lat,
                    'batry'=>$coordinate->gt_btry,
                    'accurazy'=>$coordinate->gt_accu,
                    'time'=>strtotime($coordinate->gt_time)
                ];
            });

            $productives = ProductiveVisit::with(['doctor','chemist','otherHospitalStaff'])->where('u_id',$userId)->whereBetween('pro_end_time',[$fromDateTime,$toDateTime])->get();

            $productives->transform(function($productive){

                $name = isset($productive->doctor)?$productive->doctor->doc_name:($productive->chemist?$productive->chemist->chemist_name:(isset($productive->otherHospitalStaff)?$productive->otherHospitalStaff->hos_stf_name:"Deleted"));
                $time = strtotime($productive->pro_end_time);
    
                return [
                    'lng'=>round($productive->lon,7),
                    'lat'=>round($productive->lat,7),
                    'batry'=>$productive->btry_lvl,
                    'accurazy'=>0,
                    'time'=>$time,
                    'type'=>1
                ];
            });

            $coordinates = $coordinates->concat($productives);

            $unProductives = UnproductiveVisit::with(['doctor','chemist','other_hos_staff'])->where('u_id',$userId)->whereBetween('unpro_time',[$fromDateTime,$toDateTime])->get();

            $unProductives->transform(function($unProductive){

                $name = isset($unProductive->doctor)?$unProductive->doctor->doc_name:(isset($unProductive->chemist)?$unProductive->chemist->chemist_name:(isset($unProductive->other_hos_staff)?$unProductive->other_hos_staff->hos_stf_name:"Deleted"));

                $time = strtotime($unProductive->unpro_time);

                return [
                    'lng'=>round($unProductive->lon,7),
                    'lat'=>round($unProductive->lat,7),
                    'batry'=>$unProductive->btry_lvl,
                    'accurazy'=>0,
                    'time'=>$time,
                    'type'=>0
                ];
            });

            $coordinates = $coordinates->concat($unProductives);

            $query = UserAttendance::where(function(Builder $query)use($fromDateTime){
                $query->orWhereDate('check_in_time',$fromDateTime);
                $query->orWhereDate('check_out_time',$fromDateTime);
            })->where('u_id',$userId)->get();

            $mileage_amount = 0;
            $gps_mileage = 0;

            $checkinTime = null;
            $checkoutTime = null;

            foreach ($query as  $checking) {
                if($checking->check_in_time&&!$checkinTime){
                    $checkinTime = strtotime($checking->check_in_time);

                    $coordinates->push([
                        'lng'=>(string) round($checking->check_in_lon-0.0000005,7),
                        'lat'=>(string) round($checking->check_in_lat-0.0000005,7),
                        'batry'=>$checking->check_in_battery,
                        'accurazy'=>0,
                        'time'=>$checkinTime-60
                    ]);
    
                    $coordinates->push([
                        'lng'=>(string) round($checking->check_in_lon-0.0000002,7),
                        'lat'=>(string) round($checking->check_in_lat-0.0000002,7),
                        'batry'=>$checking->check_in_battery,
                        'accurazy'=>0,
                        'time'=>$checkinTime-30
                    ]);
    
                    $coordinates->push([
                        'lng'=>(string) round($checking->check_in_lon,7),
                        'lat'=>(string) round($checking->check_in_lat,7),
                        'batry'=>$checking->check_in_battery,
                        'accurazy'=>0,
                        'time'=>$checkinTime,
                        'type'=>2
                    ]);
    
                    $coordinates->push([
                        'lng'=>(string) round($checking->check_in_lon+0.0000002,7),
                        'lat'=>(string) round($checking->check_in_lat+0.0000002,7),
                        'batry'=>$checking->check_in_battery,
                        'accurazy'=>0,
                        'time'=>$checkinTime + 30
                    ]);
    
                    $coordinates->push([
                        'lng'=>(string) round($checking->check_in_lon+0.0000005,7),
                        'lat'=>(string) round($checking->check_in_lat+0.0000005,7),
                        'batry'=>$checking->check_in_battery,
                        'accurazy'=>0,
                        'time'=>$checkinTime + 60
                    ]);
                } 
                
                if($checking->check_out_time){
                    $checkoutTime = strtotime($checking->check_out_time);
    
                    $coordinates->push([
                        'lng'=>(string) round($checking->check_out_lon-0.0000005,7),
                        'lat'=>(string) round($checking->check_out_lat-0.0000005,7),
                        'batry'=>$checking->check_out_battery,
                        'accurazy'=>0,
                        'time'=>$checkoutTime-60
                    ]);
    
                    $coordinates->push([
                        'lng'=>(string) round($checking->check_out_lon-0.0000002,7),
                        'lat'=>(string) round($checking->check_out_lat-0.0000002,7),
                        'batry'=>$checking->check_out_battery,
                        'accurazy'=>0,
                        'time'=>$checkoutTime-30
                    ]);
    
                    $coordinates->push([
                        'lng'=>(string) round($checking->check_out_lon,7),
                        'lat'=>(string) round($checking->check_out_lat,7),
                        'batry'=>$checking->check_out_battery,
                        'accurazy'=>0,
                        'time'=>$checkoutTime,
                        'type'=>3
                    ]);
    
                    $coordinates->push([
                        'lng'=>(string) round($checking->check_out_lon+0.0000002,7),
                        'lat'=>(string) round($checking->check_out_lat+0.0000002,7),
                        'batry'=>$checking->check_out_battery,
                        'accurazy'=>0,
                        'time'=>$checkoutTime+30
                    ]);
    
                    $coordinates->push([
                        'lng'=>(string) round($checking->check_out_lon+0.0000005,7),
                        'lat'=>(string) round($checking->check_out_lat+0.0000005,7),
                        'batry'=>$checking->check_out_battery,
                        'accurazy'=>0,
                        'time'=>$checkoutTime+60
                    ]);
                }
            }

            $coordinates = $coordinates->filter(function($coordinate)use($checkinTime,$checkoutTime){
                return $checkinTime&&$coordinate['time']>$checkinTime-120 && (!$checkoutTime||$checkoutTime&&$coordinate['time']<$checkoutTime+120);
            });
    
            $coordinates = $coordinates->values()->toArray();
    
            $coordinates = array_sort($coordinates,function($a,$b){
                return $a['time'] - $b['time'];
            });
    
            $coordinates = array_values($coordinates);

            $gps_mileage = $this->calculateDistance($coordinates);

            // New Gps Calculations End

            $row = [];
            $newRow = [];

            $date =  $dt->format("Y-m-d");

            // COL1 Date
            $row[] = $date;
            $newRow[] = $date;

            $itineraryDate = $itineraryDates->where('date',$date)->first();
            $expencesForDay = $expences->where('date',$date);

            $attendanceStatus = UserAttendance::where('u_id','=',$userId)->whereDate('check_in_time','=',$date)->whereNotNull('check_out_time')->latest()->first();

            $stationMileage = StationMileage::where('u_id',$userId)->where('exp_date',$dt->format('Y-m-d'))->first();

            $specialDays = SpecialDay::whereDate('sd_date',$dt->format("Y-m-d"))->get();

            $backDatedExpences = Expenses::where('u_id',$userId)->whereDate('exp_date',$date)->first();

            $vehicleTypeRate = 0;
            $mileagePay =0;
            $addtionalMileage = 0;
            $bataValue = 0;

            if($itineraryDate){

                $vehicleTypeRate = $itineraryDate['rate'];

                $bataCategory = $itineraryDate['bataType']?$itineraryDate['bataType']->bataCategory:null;

                // Showing expenses for only one of these conditions true
                //      - attendance marked
                //      - has backdated expenses
                //      - has station mileages
                //      - the day is working day
                if( strtotime($date)<=time()&&( $attendanceStatus||$backDatedExpences||$stationMileage||(!$itineraryDate['isFieldWorking']&&$itineraryDate['isWorking']))){

                    $bataValue = $itineraryDate['bataType']?$itineraryDate['bataType']->bt_value:0;

                    // COL2 Town Names
                    $row[] = $itineraryDate['isFieldWorking']? $itineraryDate['townNames'] : $itineraryDate['typeNames'] ;
                    $newRow[] = $itineraryDate['isFieldWorking']? $itineraryDate['townNames'] : $itineraryDate['typeNames'] ;
                    // COL3 Bata Type
                    $row[] = $bataCategory?$bataCategory->btc_category:"";
                    $newRow[] = $bataCategory?$bataCategory->btc_category:"";

                    // COL4 Km(s) COL5 Base Allowances
                    $row[] = "";
                    $row[] = "";
                    $newRow[] = "";
                    $newRow[] = "";

                    // Bata Categories
                    foreach($bataCategories as $bataCategory2){
                        if( $bataCategory&& $bataCategory2->getKey()==$bataCategory->getKey()){
                            $row[]= number_format($bataValue,2);
                            $newRow[]= $bataValue;
                        } else {
                            $row[] = "";
                            $newRow[] = "";
                        }
                    }

                    // COL5 Bata Total
                    $row[] = number_format($bataValue,2);
                    $newRow[] = $bataValue;

                    // COL6 Mileage
                    $row[] = $itineraryDate['mileage'];
                    $newRow[] = $itineraryDate['mileage'];

                    // COL7 Mileage Pay
                    $mileagePay = $itineraryDate['mileage']*$vehicleTypeRate;
                    // $row[] = $mileagePay+round($addtionalMileage);
                    $row[] = number_format($mileagePay,2);
                    $newRow[] = $mileagePay;

                    // COL8 Additional Mileage column
                    $addtionalKm = Expenses::where('u_id',$userId)
                        ->where('rsn_id',14)
                        ->whereDate('exp_date',$date)
                        ->sum('exp_amt');
                    $addtionalMileage = $addtionalKm*$vehicleTypeRate;
                    $sum_of_addtional += $addtionalKm;
                    $row[] = round($addtionalMileage);
                    $newRow[] = round($addtionalMileage);


                    // // COL8 Mileage Pay
                    // $mileagePay = $itineraryDate['mileage']*$vehicleTypeRate;
                    // // $row[] = $mileagePay+round($addtionalMileage);
                    // $row[] = $mileagePay;

                } else {
                    $row = [$date,$itineraryDate['townNames'] == ''?$itineraryDate['typeNames']:($itineraryDate['townNames'].' [ '.$itineraryDate['typeNames'].' ]') ,"","","","","","",""];
                    foreach($bataCategories as $bataCategory2){
                        $row[] = "";
                        $newRow[] = "";
                    }
                }

            } elseif(!$specialDays->isEmpty()){
                if($dt->format("D") == "Sat"){
                    $row[] = 'Saturday';
                    $newRow[] = 'Saturday';
                } elseif($dt->format("D") == "Sun"){
                    $row[] = 'Sunday';
                    $newRow[] = 'Sunday';
                }else {
                    $row[] = 'Holiday';
                    $newRow[] = 'Holiday';
                }
                $row = array_merge($row,array_fill(0,4+$bataCategories->count() + 3,""));
                $newRow = array_merge($newRow,array_fill(0,4+$bataCategories->count() + 3,""));
            } else {
                if($dt->format("D") == "Sun"){
                    $row[] = 'Sunday';
                    $newRow[] = 'Sunday';
                } elseif($dt->format("D") == "Sat"){
                    $row[] = 'Saturday';
                    $newRow[] = 'Saturday';
                }else {
                    $row[] = 'No Route Plan';
                    $newRow[] = 'No Route Plan';
                }

                $row = array_merge($row,array_fill(0,4+$bataCategories->count() + 3,""));
                $newRow = array_merge($newRow,array_fill(0,4+$bataCategories->count() + 3,""));
            }

            // COL9 PVT Mileage
            $row[] = "";
            $newRow[] = "";

            // GPS Mileage
            $row[] = round($gps_mileage,2);
            // $row[] = '*';
            $newRow[] = round($gps_mileage,2);

            if(strtotime($date)<=time()&&( $attendanceStatus||$backDatedExpences||$stationMileage||(!$itineraryDate['isFieldWorking']&&$itineraryDate['isWorking']))){

                // COL10 Total Mileage
                $mileageTotal = $mileagePay + $addtionalMileage;
                // $row[] = number_format($mileageTotal,2);
                $row[] = number_format($mileageTotal,2);
                $newRow[] = $mileageTotal;

                $expTotal = 0;
                foreach($expencesTypes->where('rsn_id','!=',14) as $expencesType){
                    $expencesForType = $expencesForDay->where('rsn_id',$expencesType->getKey())->sum('exp_amt');
                    $expTotal+= $expencesForType;
                    $row[] = number_format($expencesForType,2);
                    $newRow[] = $expencesForType;
                }

                // COL11 Expenses total
                $row[] = number_format($expTotal,2);
                $newRow[] = $expTotal;

                $grand_total = $expTotal+$bataValue+$mileageTotal;
                $row[] = number_format($grand_total,2);
                $newRow[] = $grand_total;
            } else {
                $row[] = "";
                $row[] = "";
                $row[] = "";

                $newRow[] = "";
                $newRow[] = "";
                $newRow[] = "";

                foreach($expencesTypes->where('rsn_id','!=',14) as $expencesType){
                    $row[] = "";
                    $newRow[] = "";
                }
            }

            $rows[] = $row;
            $newRows[] = $newRow;

        }

        if(empty($rows))
            throw new WebAPIException("Empty results. No Expences found!");

        if(empty($newRows))
            throw new WebAPIException("Empty results. No Expences found!");

        $totalRow = [];
        foreach ($newRows as $key=>  $row) {
            foreach($row as $keyCell=> $cell){
                if($keyCell>3){
                    if(!isset($totalRow[$keyCell]))
                        $totalRow[$keyCell ] = 0;
                    $totalRow[$keyCell] += round((float)$cell,2);
                }
                else{
                    $totalRow[$keyCell] = "";
                }
            }
        }

        $base_allow = $totalRow[8];
        $mileage_pay_grand_tot = $totalRow[10];
        $mileage_grand_tot = $totalRow[14];
        // $grand_tot = $totalRow[20];
        // COL Base allowanse
        $totalRow[4] = $user->base_allowances;
        // Added base allowance to bata total
        $totalRow[8] = number_format( str_replace(',','',$base_allow) + $user->base_allowances,2);
        //
        $totalRow[10] = number_format($mileage_pay_grand_tot,2);
        //
        $totalRow[14] = number_format($mileage_grand_tot,2);
        //
        // $totalRow[20] = number_format($grand_tot,2);

        if($vehicleTypeRate == 0){
            $vehicleTypeRateInst = VehicleTypeRate::where('vht_id',$user->vht_id)->whereDate('vhtr_srt_date','<=',$date)->where('u_tp_id',$user->u_tp_id)->latest()->first();
            $vehicleTypeRate = $vehicleTypeRateInst?$vehicleTypeRateInst->vhtr_rate:0;
        }

        $totalRow[9+$bataCategories->count()] = $user->u_pvt_mileage_limit*$vehicleTypeRate;
        $totalRow[10+$bataCategories->count()] += round($gps_mileage);
        $totalRow[11+$bataCategories->count()] = str_replace(',','',$totalRow[11+$bataCategories->count()]) + ($user->u_pvt_mileage_limit*$vehicleTypeRate);

        $totalRow[1] = "Grand Total";

        $totalRow[count( array_keys( $totalRow))-1] += $user->base_allowances+($user->u_pvt_mileage_limit*$vehicleTypeRate);

        $rows[] = $totalRow;
        $sum_of_private += $user->u_pvt_mileage_limit;

        $team_limit = TeamUser::with('team')->where('u_id',$user->id)->first();
        
        return [
            'results'=>$rows,
            'count'=>0,
            'sum_addtional' => $sum_of_addtional,
            'sum_private' =>$sum_of_private,
            'sum_official'=>$totalRow[$bataCategories->count()+6],
            'day_mileage_limit' => isset($team_limit->team->tm_mileage_limit)?$team_limit->team->tm_mileage_limit:null
        ];
    }


    protected function getAdditionalHeaders($request){
        // $bataCount = BataType::count();
        $bataCatCount = BataCategory::count();
        $expencesCount = Reason::latest()->where('rsn_type',config('shl.expenses_reason_type'))->where('rsn_id','!=',14)->count();

        $columns = [[
            [
                "title"=>"",
                "colSpan"=>5
            ],
            [
                "title"=>"Bata (Rs)",
                "colSpan"=>$bataCatCount+1
            ],
            [
                "title"=>"Mileage pay (Km)",
                "colSpan"=>6
            ],
            [
                "title"=>"Other Types",
                "colSpan"=>$expencesCount+1
            ],
            [
                "title"=>""
            ]
        ]];

        return $columns;
    }

    protected function setColumns($columnController, Request $request){
        $columnController->text('0')->setLabel("Date")->setSearchable(false);
        $columnController->text('1')->setLabel("Town")->setSearchable(false);
        $columnController->text("2")->setLabel("Station")->setSearchable(false);
        $columnController->text("3")->setLabel("Km(s)")->setSearchable(false);
        $columnController->number("4")->setLabel("Base Allowance")->setSearchable(false);

        $currentKey = 4;
        $bataCatTypes = BataCategory::get();
        foreach ($bataCatTypes as $key => $bataCatType) {
            $currentKey++;
            $columnController->text((string)$currentKey)->setLabel($bataCatType->btc_category)->setSearchable(false);
        }

        $columnController->number((string)++$currentKey)->setLabel('Total');


        $columnController->text((string)++$currentKey)->setLabel("Mileage")->setSearchable(false);
        $columnController->number((string)++$currentKey)->setLabel("Mileage Pay")->setSearchable(false);
        $columnController->text((string)++$currentKey)->setLabel("Additional Mileage")->setSearchable(false);
        $columnController->text((string)++$currentKey)->setLabel("Private Mileage")->setSearchable(false);
        $columnController->text((string)++$currentKey)->setLabel("GPS Mileage")->setSearchable(false);
        $columnController->number((string)++$currentKey)->setLabel("Mileage Total")->setSearchable(false);

        $reasons = Reason::latest()->where('rsn_type',config('shl.expenses_reason_type'))->where('rsn_id','!=',14)->get();
        foreach ($reasons as $key => $reason) {
            $columnController->text((string)++$currentKey)->setLabel($reason->rsn_name)->setSearchable(false);
        }

        $columnController->number((string)++$currentKey)->setLabel("Total")->setSearchable(false);

        $columnController->number((string)++$currentKey)->setLabel("Grand Total")->setSearchable(false);

    }


    protected function setInputs($inputController){
        $inputController->ajax_dropdown("team")->setLabel("Team")->setLink("team");
        $inputController->ajax_dropdown('user')->setWhere(["tm_id" => "{team_id}",'u_tp_id'=> '3|2'.'|'.config('shl.product_specialist_type'),'divi_id'=>'{divi_id}'])->setLabel('MR/PS or FM')->setLink('user');
        $inputController->ajax_dropdown("division")->setLabel("Division")->setLink("division");
        $inputController->date("s_date")->setLabel("From");
        $inputController->date("e_date")->setLabel("To");
        $inputController->setStructure([["team_id","divi_id","user"],["s_date","e_date"]]);
    }

    public function getTypesAndReasons(){

        $reasons = Reason::latest()->where('rsn_type',config('shl.expenses_reason_type'))->where('rsn_id','!=',14)->get();
        $reasons->transform(function($reason){
            return [
                'label'=>$reason->rsn_name,
                'value'=>$reason->getKey()
            ];
        });

        $bataTypes = BataType::all();

        $bataTypes->transform(function($bataType){
            return [
                'label'=>$bataType->bt_name,
                'value'=>$bataType->getKey()
            ];
        });

        $bataCategory = BataCategory::all();
        $bataCategory->transform(function($btc){
            return [
                'label'=>$btc->btc_category,
                'value'=>$btc->getKey()
            ];
        });

        return response()->json([
            'success'=>true,
            'reasons'=>$reasons,
            'bataTypes'=>$bataTypes,
            'bataCategory'=>$bataCategory
        ]);
    }

    public function search(Request $request){
        $values = $request->input('values',[]);

        $formatedResult = [];

        $result = $this->__searchBy($values);

        foreach($result['results'] as $row){
            $newRow = $row;

            foreach ($row as $key => $cell) {

                $style = ($key == 0 || $key == 1 || $key == 8 || $key == 14 || $key == 19) && $row[1] != "Grand Total"?
                        [
                            'color'=> 'black',
                            'background'=> '#e4e8f0',
                            'border'=> '1px solid #fff'
                        ]
                    :
                        $row[1] == "Grand Total" ?
                        [
                            'color'=> 'black',
                            'background'=> '#add0f0',
                            'border'=> '1px solid #e8e8e8'
                        ]
                    :
                        $key == 20?
                        [
                            'color'=> 'black',
                            'background'=> '#bcc0c4',
                            'border'=> '1px solid #e8e8e8'
                        ]
                    : [
                        'color'=> 'black',
                        'background'=> 'fffefa',
                        'border'=> '1px solid #e8e8e8'
                    ];

                $newRow[$key.'_style'] = $style;
            }

            $formatedResult[] = $newRow;
        }

        $count = count(array_keys($row));

        $formatedResult[] = [
            "0"=>"",
            "0"=>"",
            "0_colspan"=>$count
        ];


        $formatedResult[] = [
            "0"=>"Kilometers",
            "0_colspan"=>4,
            "0_style"=>[
                "background"=>"#bcc0c4",
                "text-align"=>"center"
            ],
            "1"=>"",
            "1_colspan"=>$count-4
        ];

        $formatedResult[]= [
            "0"=>"Official",
            "0_colspan"=>2,
            "1"=>$result['sum_official'],
            "1_colspan"=>2,
            "2"=>"",
            "2_colspan"=>$count-4
        ];

        $formatedResult[]= [
            "0"=>"Additional",
            "0_colspan"=>2,
            "1"=>$result['sum_addtional'],
            "1_colspan"=>2,
            "2"=>"",
            "2_colspan"=>$count-4
        ];

        $formatedResult[]= [
            "0"=>"Private",
            "0_colspan"=>2,
            "1"=>$result['sum_private'],
            "1_colspan"=>2,
            "2"=>"",
            "2_colspan"=>$count-4
        ];


        $formatedResult[]= [
            "0"=>"Total",
            "0_colspan"=>2,
            "0_style"=>[
                "background"=>"#bcc0c4"
            ],
            "1"=>$result['sum_private']+$result['sum_addtional']+$result['sum_official'],
            "1_colspan"=>2,
            "1_style"=>[
                "background"=>"#bcc0c4"
            ],
            "2"=>"",
            "2_colspan"=>$count-4
        ];


        $formatedResult[] = [
            "0"=>"",
            "0"=>"",
            "0_colspan"=>$count
        ];

        return [
            'results'=>$formatedResult
        ];
    }

    public function searchReport(Request $request){
        $values = $request->input('values',[]);

        return $this->__searchBy($values);
    }

    public function searchTeamMembers(Request $request){
        $keyword = $request->input('keyword',"");
        $tm_id = $request->input('where.tm_id.value');

        if($request->has('where.tm_id.value'))
        {
            $teamUsers = TeamUser::where('tm_id',$tm_id)->get()->pluck('u_id')->all();
            $team = Team::find($tm_id);
            if($team){
                $teamUsers[] = $team->fm_id;
            }

            $users = User::where(function($query)use($keyword){
                $query->orWhere('name','LIKE',"%$keyword%");
                $query->orWhere('email','LIKE',"%$keyword%");
                $query->orWhere('user_name','LIKE',"%$keyword%");
                $query->orWhere('u_code','LIKE',"%$keyword%");
            })->whereIn('id',$teamUsers)->get();
        } else {
            $users = User::where(function($query)use($keyword){
                $query->orWhere('name','LIKE',"%$keyword%");
                $query->orWhere('email','LIKE',"%$keyword%");
                $query->orWhere('user_name','LIKE',"%$keyword%");
                $query->orWhere('u_code','LIKE',"%$keyword%");
            })->limit(30)->get();
        }

        $users->transform(function($user){
            return [
                'value'=>$user->getKey(),
                'label'=>$user->name
            ];
        });

        return $users;
    }

    public function getMileage($table_name,$checkIn,$checkOut,$u_id){
        $mileage = 0;

        try {
            $gps_for_day = DB::table($table_name)
                    ->select('gt_lon','gt_lat','gt_time')
                    ->where('u_id',$u_id)
                    ->whereBetween('gt_time',[$checkIn->format('Y-m-d 00:00:00'),$checkOut->format('Y-m-d 23:59:59')])
                    // ->orderBy('gt_time','desc')
                    ->get();


            for ($i=0; $i < $gps_for_day->count()-2; $i++) {
                $point1 = array("lat" => $gps_for_day[$i]->gt_lat, "long" => $gps_for_day[$i]->gt_lon);
                $point2 = array("lat" => $gps_for_day[$i+1]->gt_lat, "long" => $gps_for_day[$i+1]->gt_lon);

                // $mileage += $this->distanceCalculation($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
                $mileage += $this->distance($point1['lat'], $point1['long'], $point2['lat'], $point2['long'],"K");
            }
        } catch (\Exception $e) {

        }
        return json_encode($mileage);
    }

    function distance($lat1, $lon1, $lat2, $lon2, $unit) {

        $R = 6371; // km
        $dLat = deg2rad($lat2-$lat1);
        $dLon = deg2rad($lon2-$lon1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2); 

        $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);

        $c = 2 * atan2(sqrt($a),sqrt(1-$a));
        $d = $R * $c;

        return $d;
    }

    //  new Gps calculations

    protected function getMonthsBetween($a,$b){
        
        $i = date("Y_m", strtotime($a));
        while($i <= date("Y_m", strtotime($b))){
            $months[] = $i;
            if(substr($i, 4, 2) == "12")
                $i = (date("Y", strtotime($i."01")) + 1)."01";
            else
                $i++;
        }

        return $months;
    }

    protected function calculateDistance($coordinates){
        $distanceTotal = 0;

        try {
            foreach ($coordinates as $key => $value) {
                $distanceTotal += $this->distance($coordinates[$key]['lat'], $coordinates[$key]['lng'], $coordinates[$key+1]['lat'], $coordinates[$key+1]['lng'],"K");
            }
        } catch (\Exception $e) {
            
        }

        return json_encode($distanceTotal);
    }

    // 
}
