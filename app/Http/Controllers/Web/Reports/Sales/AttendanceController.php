<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
use App\Models\VehicleTypeRate;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAttendance;
use App\Models\Area;

class AttendanceController extends ReportController
{
    use Territory;

    protected $title = "SR Attendance Report";

    public function search(Request $request)
    {

        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'checkin':
                $sortBy = 'check_in_time';
                break;
            case 'checkout':
                $sortBy = 'check_out_time';
                break;
            case 'user_code':
                $sortBy = 'u_code';
                break;
            case 'app_version':
                $sortBy = 'app_version';
                break;
            default:
                $sortBy = 'u_code';
                break;
        }

        $query = DB::table('user_attendance as ua')
            ->select('ua.u_id', 'ua.check_in_time', 'ua.check_out_time', 'ua.check_in_lat', 'ua.check_in_lon', 'ua.check_out_lat', 'ua.check_out_lon', 'ua.app_version', 'u.id', 'u.name', 'u.u_code', 'u.u_tp_id')
            ->join('users as u', 'ua.u_id', 'u.id')
            ->where('u.u_tp_id', 10)
            ->whereNull('u.deleted_at')
            ->whereNull('ua.deleted_at');

        if (isset($values['s_date']) && isset($values['e_date'])) {
            $query->whereDate('ua.check_in_time', ">=", date("Y-m-d", strtotime($values['s_date'])));
            $query->whereDate('ua.check_in_time', "<=", date("Y-m-d", strtotime($values['e_date'])));
        }

        if (isset($values['user'])) {
            $query->where('ua.u_id', $values['user']['value']);
        } else {
            $user = Auth::user();

            if (in_array($user->getRoll(), [config('shl.sales_rep_type')])) {
                $users = UserModel::getByUser($user);
                $query->whereIn('ua.u_id', $users->pluck('u_id')->all());
            }

            if ($user->getRoll() == config('shl.area_sales_manager_type')) {
                $userCode = substr($user->u_code, 0, 4);
                $area = Area::where('ar_code', $userCode)->first();
                if (isset($area->ar_code)) {
                    $users = User::where('u_code', 'LIKE', '%' . $area->ar_code . '%')->get();
                    $query->whereIn('u_id', $users->pluck('id')->all());
                }
            }
        }

        if (date('m', strtotime($values['s_date'])) != date('m')) {
            $table_name = 'gps_tracking_' . date('Y', strtotime($values['s_date'])) . '_' . date('m', strtotime($values['s_date']));
        } else {
            $table_name = 'gps_tracking';
        }
        $query->orderBy('ua.check_in_time', 'ASC');
        $count = $this->paginateAndCount($query, $request, $sortBy);


        $results =  $query->get();

        $results->transform(function ($attendance) use ($table_name) {

            $label = "";
            $gps_mileage = 0;
            $mileage_amount = 0;

            $checkIn = $attendance->check_in_time;
            $checkOut = $attendance->check_out_time;


            // if(isset($checkOut)){
            //      $gps_mileage = $this->getMileage($table_name,$checkIn,$checkOut,$attendance->id);
            // }

            // if(isset($gps_mileage)){
            //      $mileage_amount = $gps_mileage;
            // }

            $check_in_lat = $attendance->check_in_lat;
            $check_in_lon = $attendance->check_in_lon;

            $check_out_lat = $attendance->check_out_lat;
            $check_out_lon = $attendance->check_out_lon;

            $map_in = $check_in_lat . ',' . $check_in_lon;
            $map_out = $check_out_lat . ',' . $check_out_lon;

            if ($check_out_lat == null & $check_out_lon == null) {
                $label = "";
            }
            if ($check_out_lat != null & $check_out_lon != null) {
                $label = "Location Out";
            }

            $arr2 = str_split($attendance->u_code, 4);

            $area = Area::where('ar_code', $arr2[0])->first();

            return [
                'user' => $attendance->name,
                'user_code' => $attendance->u_code ? $attendance->u_code : "-",
                'checkin' => $checkIn ? $checkIn : null,
                'checkout' => $checkOut ? $checkOut : null,
                'app_version' => $attendance->app_version,
                'area' => isset($area->ar_name) ? $area->ar_name : "-",
                'view_map_in' => [
                    'label' => 'Location In',
                    'link' => 'https://www.google.com/maps/search/?api=1&query=' . $map_in,
                ],
                'view_map_out' => [
                    'label' => $label,
                    'link' => 'https://www.google.com/maps/search/?api=1&query=' . $map_out
                ],
                // 'mileage_amount' => round($mileage_amount)
            ];
        });

        return [
            'count' => $count,
            'results' => $results
        ];
    }

    public function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long)
    {
        // Calculate the distance in degrees
        $degrees = rad2deg(acos((sin(deg2rad($point1_lat)) * sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat)) * cos(deg2rad($point2_lat)) * cos(deg2rad($point1_long - $point2_long)))));
        // $distance = $degrees * 69.05482; // 1 degree = 69.05482 miles, based on the average diameter of the Earth (7,913.1 miles)
        $distance = $degrees * 1.609344;
        return $distance;
    }

    public function getMileage($table_name, $checkIn, $checkOut, $u_id)
    {
        $mileage = 0;

        try {
            $gps_for_day = DB::table($table_name)
                ->select('gt_lon', 'gt_lat', 'gt_time')
                ->where('u_id', $u_id)
                ->whereBetween('gt_time', [$checkIn->format('Y-m-d 00:00:00'), $checkOut->format('Y-m-d 23:59:59')])
                ->get();


            for ($i = 0; $i < $gps_for_day->count() - 2; $i++) {
                $point1 = array("lat" => $gps_for_day[$i]->gt_lat, "long" => $gps_for_day[$i]->gt_lon);
                $point2 = array("lat" => $gps_for_day[$i + 1]->gt_lat, "long" => $gps_for_day[$i + 1]->gt_lon);

                // $mileage += $this->distanceCalculation($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
                $mileage += $this->distance($point1['lat'], $point1['long'], $point2['lat'], $point2['long'], "K");
            }
        } catch (\Exception $e) {
        }
        return json_encode($mileage);
    }

    function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
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

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text("user")->setLabel("SR");
        $columnController->text("user_code")->setLabel("SR Code");
        $columnController->text("checkin")->setLabel("Checkin Time");
        $columnController->text("checkout")->setLabel("Checkout Time");
        $columnController->text("area")->setLabel("Area");
        $columnController->text("app_version")->setLabel("App Version");
        $columnController->link("view_map_in")->setDisplayLabel("Checkin Location")->setLabel("Checkin Location");
        $columnController->link("view_map_out")->setDisplayLabel("Checkout Location")->setLabel("Checkout Location");
        //    $columnController->text("mileage_amount")->setLabel("Gps Mileage");
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown("user")->setWhere(['u_tp_id' => '10'])->setLabel("MR/PS or FM")->setLink("user");
        $inputController->date("s_date")->setLabel("From");
        $inputController->date("e_date")->setLabel("To");

        $inputController->setStructure([["team", "user"], ["s_date", "e_date"]]);
    }
}
