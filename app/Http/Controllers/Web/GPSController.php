<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\GPSTracking;
use Illuminate\Support\Facades\DB;
use App\Models\ProductiveVisit;
use App\Models\UnproductiveVisit;
use App\Models\UserAttendance;
use Illuminate\Database\Eloquent\Builder;

class GPSController extends Controller{
    public function search(Request $request){
        $validator = Validator::make($request->all(),[
            'user'=>'required|array',
            'user.value'=>'required|numeric|exists:users,id',
            'date'=>'required|date_format:Y-m-d'
        ]);

        if($validator->fails()){
            throw new WebAPIException($validator->errors()->first());
        }

        $date = $request->input('date');
        $user = $request->input('user');
        $userId = $user['value'];

        $fromDateTime = date('Y-m-d 00:00:00',strtotime($date));
        $toDateTime = date('Y-m-d 23:59:59',strtotime($date));

        $months = $this->getMonthsBetween($fromDateTime,$toDateTime);

        if(count($months)==1&&$months[0]==date('Y_m')){
            $coordinates = GPSTracking::where('u_id',$userId)->whereBetween('gt_time',[$fromDateTime,$toDateTime])->get();
            
        } else {

            $coordinates = collect([]) ;

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
                'title'=>$productive->pro_visit_no,
                'description'=>$name.' <br/>@ '.date("H:i:s",$time),
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
                'title'=>$unProductive->un_visit_no,
                'description'=>$name.' <br/>@ '.date("H:i:s",$time),
                'type'=>0
            ];
        });

        $coordinates = $coordinates->concat($unProductives);

        $checkings = UserAttendance::where(function(Builder $query)use($fromDateTime){
            $query->orWhereDate('check_in_time',$fromDateTime);
            $query->orWhereDate('check_out_time',$fromDateTime);
        })->where('u_id',$userId)->get();

        $checkinTime = null;
        $checkoutTime = null;

        foreach ($checkings as  $checking) {
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
                    'title'=>"Checkin",
                    'description'=>"@ ".date('H:i:s',$checkinTime),
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
                    'title'=>"Checkout",
                    'description'=>"@ ".date('H:i:s',$checkoutTime),
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

        $from = time();

        if(isset($coordinates[0]))
            $from = $coordinates[0]['time'];

        return response()->json([
            'coordinates'=>$coordinates,
            'startTime'=>$from,
            'checkin'=> $checkinTime? date('H:i:s',$checkinTime):"",
            'checkout'=> $checkoutTime? date('H:i:s',$checkoutTime):""
        ]);
    }

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
}