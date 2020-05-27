<?php

namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use App\Models\UserAttendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Form\Columns\ColumnController;
use App\Models\UserArea;
use App\Traits\Territory;
use App\CSV\UserArea as AppUserArea;
use App\Models\ProductiveVisit;
use App\Models\UnproductiveVisit;
use App\Models\UserCustomer;
use App\Models\User;
// use App\CSV\DoctorSubTown;
use App\Models\DoctorSubTown;
use App\Models\Chemist;
use App\Models\OtherHospitalStaff;
use App\Models\TeamUser;
use App\Models\UserTeam;
use App\Models\VehicleTypeRate;

class AttendanceController extends ReportController
{
    use Territory;

    protected $title = "MR/PS Attendance Report";

    public function search(Request $request){

        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'checkin':
                $sortBy = 'ua.check_in_time';
                break;
            case 'checkout':
                $sortBy = 'ua.check_out_time';
                break;
            default:
                $sortBy = 'ua.check_in_time';
                break;
        }

        // $query = UserAttendance::query();
        // $query->where('u_id','!=',2);
        // $query->where('u_id','!=',5);

        $query = DB::table('user_attendance as ua');
        $query->select('u.id','u.u_code','u.name','ua.check_in_time','ua.check_out_time','ua.check_out_lat','ua.check_out_lon','ua.check_in_lat','ua.check_in_lon','ua.u_id','ua.app_version');
        $query->join('users as u','u.id','ua.u_id');
        $query->where('u_id','!=',2);
        $query->where('u_id','!=',5);
        $query->where('u.u_tp_id','!=',15);
        $query->where('u.u_tp_id','!=',10);
        $query->whereNull('ua.deleted_at');
        $query->whereNull('u.deleted_at');

        if(isset($values['s_date'])&&isset($values['e_date'])){
            $query->whereDate('ua.check_in_time',">=",date("Y-m-d",strtotime($values['s_date'])));
            $query->whereDate('ua.check_in_time',"<=",date("Y-m-d",strtotime($values['e_date'])));
        }

        if(isset($values['user'])){
            $query->where('ua.u_id',$values['user']['value']);
        } else {
            $user = Auth::user();
            /** @var \App\Models\User $user */

            if(in_array($user->getRoll(),[
                config('shl.product_specialist_type'),
                config('shl.medical_rep_type'),
                config('shl.field_manager_type')
            ])){
                $users = UserModel::getByUser($user);
                $query->whereIn('ua.u_id',$users->pluck('u_id')->all());
            }


            $teams = UserTeam::where('u_id',$user->getKey())->get();
            if($teams->count()){
                $users = TeamUser::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
                $query->whereIn('ua.u_id',$users->pluck('u_id')->all());
            }  
        }

        if(date('m',strtotime($values['s_date'])) != date('m')){
            $table_name = 'gps_tracking_'.date('Y',strtotime($values['s_date'])).'_'.date('m',strtotime($values['s_date']));
        } else {
            $table_name = 'gps_tracking';
        }
        $query->orderBy('ua.check_in_time','ASC');
        $count = $this->paginateAndCount($query,$request,$sortBy);

        // $query->with('user');

        $results =  $query->get();

        // return $results;

        $results->transform(function($attendance)use($table_name){

            $label = "";
            $gps_mileage = 0;
            $mileage_amount = 0;

            $user_new = User::find($attendance->id);
            if(!isset($user_new))
                return null;

            $checkIn = $attendance->check_in_time;
            $checkOut = $attendance->check_out_time;

            $user = User::with(['teamUser','teamUser.team','fmTeam'])->find($attendance->id);

            if(isset($checkOut)){
                $gps_mileage = $this->getMileage($table_name,$checkIn,$checkOut,$attendance->id);
            }

            $vehicleTypeRateInst = VehicleTypeRate::where('vht_id',$user->vht_id)->whereDate('vhtr_srt_date','<=',$attendance->check_in_time)->where('u_tp_id',$user->u_tp_id)->latest()->first();
            $vehicleTypeRate = $vehicleTypeRateInst?$vehicleTypeRateInst->vhtr_rate:0;
            // var_dump($gps_mileage);die;

            if(isset($gps_mileage)){
                $mileage_amount = $gps_mileage;
            }


            $check_in_lat = $attendance->check_in_lat;
            $check_in_lon = $attendance->check_in_lon;

            $check_out_lat = $attendance->check_out_lat;
            $check_out_lon = $attendance->check_out_lon;

            $map_in = $check_in_lat.','.$check_in_lon;
            $map_out = $check_out_lat.','.$check_out_lon;

            if($check_out_lat == null & $check_out_lon == null){
                $label = "";
            }
            if($check_out_lat != null & $check_out_lon != null) {
                $label = "Location Out";
            }
            // Getting itinerary areas for the date
            try{
                $itineraryTowns = $this->getTerritoriesByItinerary($user,strtotime($checkIn));
            } catch(\Throwable $e){
                $itineraryTowns = collect();
            }

            $itinerarySubTownIds = $itineraryTowns->pluck('sub_twn_id')->all();

            // Area names
            $areas_new = $itineraryTowns->unique('ar_id');

            $areas_new->transform(function($area){
                return $area->ar_name;
            });

            $areaNames = implode(', ',$areas_new->all());

            // Productive and unproductive counts between chekin time and chekout time
            if(isset($checkOut)){
                $chek_out = date('Y-m-d H:i:s',strtotime($checkOut));
            } else {
                $chek_out = date('Y-m-d H:i:s');
            }
            $productive =  ProductiveVisit::where('u_id',$attendance->id)
                            ->whereBetween('pro_start_time',[date('Y-m-d',strtotime($checkIn)),$chek_out])
                            ->count();

            $unproductive = UnproductiveVisit::where('u_id',$attendance->id)
                            ->whereBetween('unpro_time',[date('Y-m-d',strtotime($checkIn)),$chek_out])
                            ->count();

            // Assigned customers for user
            $assignedCustomers = UserCustomer::getByUser($user);
            $doctorIds = $assignedCustomers->pluck('doc_id')->filter(function($doctor){ return !!$doctor;})->all();
            $chemistIds = $assignedCustomers->pluck('chemist_id')->filter(function($chemist){ return !!$chemist;})->all();

            // Chemist by itinerary areas
            $chemists = Chemist::whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('chemist_id',$chemistIds)->count();

            // Doctors by institution
            $doctorsByInstitution = DB::table('doctor_intitution AS ti')
            ->join('institutions AS i','i.ins_id','=','ti.ins_id','inner')
            ->whereIn('i.sub_twn_id',$itinerarySubTownIds)
            ->where([
                'i.deleted_at'=>null,
                'ti.deleted_at'=>null
            ])
            ->whereIn('doc_id',$doctorIds)
            ->count(DB::raw('DISTINCT ti.doc_id '));

            $totalCount = $chemists + $doctorsByInstitution;

            // Doctors by sub towns
            $doctorsBySubTown = DoctorSubTown::whereIn('sub_twn_id',$itinerarySubTownIds)->whereIn('doc_id',$doctorIds)->count(DB::raw('DISTINCT doc_id'));

            $totalCount += $doctorsBySubTown;

            // Other hospital staffs by sub town
            $otherHospitalStaffs = OtherHospitalStaff::whereIn('sub_twn_id',$itinerarySubTownIds)->count();

            $totalCount += $otherHospitalStaffs;

            $completed = $productive + $unproductive;
            $missed_new =  $totalCount - $completed;

            // Users team
            $team = null;
            if(isset($user->teamUser)&&isset($user->teamUser->team)){
                $team = $user->teamUser->team;
            } else if(isset($user->fmTeam)){
                $team = $user->fmTeam;
            }

            return [
                'team_name'=>$team?$team->tm_name:"DELETED",
                "team_code"=>$team?$team->tm_code:"DELETED",
                'user'=>[
                    'label'=>$attendance->name?$attendance->name:"DELETED",
                    'value'=>$attendance->id?$attendance->id:0
                ],
                'user_code'=>$attendance->u_code?$attendance->u_code:"DELETED",
                'checkin'=>$checkIn?date('Y-m-d H:i:s',strtotime($checkIn)):null,
                'checkout'=>$checkOut?date('Y-m-d H:i:s',strtotime($checkOut)):null,
                'area' => $areaNames,
                'productive' => $productive,
                'unproductive' => $unproductive,
                'sheduled_calls' => $totalCount,
                'missed' => $missed_new,
                'app_version'=>$attendance->app_version,
                'view_map_in' => [
                    'label' => 'Location In',
                    'link' => 'https://www.google.com/maps/search/?api=1&query='.$map_in,
                ],
                'view_map_out' => [
                    'label' => $label,
                    'link' => 'https://www.google.com/maps/search/?api=1&query='.$map_out
                ],
                'mileage_amount' => number_format($mileage_amount,2),
                'mileage_amount_new' => $mileage_amount,
                // 'test_json' => [
                //     'label' => 'location json',
                //     'link' => 'https://maps.googleapis.com/maps/api/place/nearbysearch/json?key=AIzaSyA-HZSjzxUX6UhTydoDVVTdfipFI-4Wzww&radius=1&location='.$map_in
                // ]
            ];
        });

        $results = $results->filter(function($attendance){
            return !!$attendance;
        });

        $row=[];
        $row=[

            'special' => true,
            'team_name' =>NULL,
            'team_code' => NULL,
            'user'=> NULL,
            'user_code'=> NULL,
            'checkin'=> NULL,
            'checkout'=> NULL,
            'area'=> NULL,
            'productive'=> $results->sum('productive'),
            'unproductive'=>$results->sum('unproductive'),
            'sheduled_calls'=> NULL,
            'missed'=> NULL,
            'app_version'=> NULL,
            'view_map_in'=> NULL,
            'view_map_out'=> NULL,
            'mileage_amount' => number_format($results->sum('mileage_amount_new'),2),

        ];

        $results->push($row);

        return [
            'results'=>$results->values(),
            'count'=>$count

        ];

    }

    public function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long) {
        // Calculate the distance in degrees
        $degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));
        // $distance = $degrees * 69.05482; // 1 degree = 69.05482 miles, based on the average diameter of the Earth (7,913.1 miles)
        $distance = $degrees * 1.609344;
        return $distance;
    }

    public function getMileage($table_name,$checkIn,$checkOut,$u_id){
        $mileage = 0;

        try {
            $gps_for_day = DB::table($table_name)
                    ->select('gt_lon','gt_lat','gt_time')
                    ->where('u_id',$u_id)
                    ->whereBetween('gt_time',[date('Y-m-d 00:00:00',strtotime($checkIn)),date('Y-m-d 23:59:59',strtotime($checkOut))])
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

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text("team_name")->setLabel("Team Name");
        $columnController->text("team_code")->setLabel("Team Code");
        $columnController->ajax_dropdown("user")->setLabel("MR or FM");
        $columnController->text("user_code")->setLabel("User Code");
        $columnController->text("checkin")->setLabel("Checkin Time");
        $columnController->text("checkout")->setLabel("Checkout Time");
        $columnController->text("area")->setLabel("Area");
        $columnController->text('productive')->setLabel("Productive");
        $columnController->text('unproductive')->setLabel("Unproductive");
        $columnController->text('sheduled_calls')->setLabel("Sheduled Calls");
        $columnController->text('missed')->setLabel("Missed");
        $columnController->text("app_version")->setLabel("App Version");
        $columnController->link("view_map_in")->setDisplayLabel("Checkin Location")->setLabel("Checkin Location");
        $columnController->link("view_map_out")->setDisplayLabel("Checkout Location")->setLabel("Checkout Location");
        $columnController->number("mileage_amount")->setLabel("Gps Mileage");
    }

    public function setInputs($inputController){
        // $inputController->ajax_dropdown("user")->setLabel("MR or FM")->setLink("user");
        $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team')->setValidations('');
        $inputController->ajax_dropdown("user")->setWhere(['tm_id'=>"{team}",'u_tp_id'=>'2|3'.'|'.config('shl.product_specialist_type')])->setLabel("PS/MR or FM")->setLink("user")->setValidations('');
        $inputController->date("s_date")->setLabel("From");
        $inputController->date("e_date")->setLabel("To");

        $inputController->setStructure([["team","user"],["s_date","e_date"]]);
    }
}
