<?php
namespace App\Http\Controllers\Web\Sales;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\GPSTracking;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SalesItineraryDateDayType;
use App\Models\SfaExpenses;
use App\Models\SfaSalesOrder;
use App\Models\SfaUnproductiveVisit;
use App\Models\User;
use App\Models\UserAttendance;
use App\Models\VehicleTypeRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use \Illuminate\Support\Facades\Auth;

class ExpensesEditController extends Controller
{
    public function search(Request $request)
    {
        $rep = $request->input('rep.value');
        $month = $request->input('month');

        if (!isset($rep)) {
            throw new WebAPIException("Sale Rep Field is required");
        }

        $itinerary = SalesItinerary::where('u_id', $rep)
            ->where('s_i_year', date('Y', strtotime($month)))
            ->where('s_i_month', date('m', strtotime($month)))
            ->latest()
            ->first();

        $expenses = SfaExpenses::with('user', 'bataType')
            ->where('u_id', $rep)
            ->whereDate('exp_time', '>=', date('Y-m-01', strtotime($month)))
            ->whereDate('exp_time', '<=', date('Y-m-t', strtotime($month)))
            ->get();

        $user = User::with('vehicle_type')->where('id', $rep)->first();

        $rate = 0;
        if (isset($user->vehicle_type)) {
            $vhType = VehicleTypeRate::where('vht_id', $user->vehicle_type->vht_id)->where('u_tp_id', 10)->latest()->first();
        }

        if (isset($vhType)) {
            $rate = $vhType->vhtr_rate;
        }

        $fromDate = date('Y-m-d 00:00:00', strtotime($month));
        $toDate = date('Y-m-d 23:59:59', strtotime($month));

        $months = $this->getMonthsBetween($fromDate, $toDate);

        $expenses->transform(function ($val) use ($itinerary, $rate, $rep, $months) {

            $itineraryDate = SalesItineraryDate::with('route', 'salesItineraryDateDayTypes')
                ->where('s_i_id', $itinerary['s_i_id'])
                ->where('s_id_date', date('d', strtotime($val->exp_time)))
                ->first();

            $dayTypes = SalesItineraryDateDayType::with('dayType')->where('s_id_id', $itineraryDate['s_id_id'])->first();

            $userMilage = $this->getUserGpsMilage($months, $val->exp_time, $rep);
            //   return $userMilage;

            $mileage = 0;

            if (isset($userMilage)) {
                $mileage = $userMilage;
            }

            return [
                'date' => date('Y-m-d', strtotime($val->exp_time)),
                'bataType' => [
                    'label' => $val->bataType->bt_name,
                    'value' => $val->bataType->bt_id,
                ],
                'stationery' => $val->stationery,
                'parking' => $val->parking,
                'user' => [
                    'label' => $val->user->name,
                    'value' => $val->user->id,
                ],
                'remark' => $val->remark,
                'app' => $val->app_version,
                'mileage' => $val->mileage,
                'exp_id' => $val->sfa_exp_id,
                'status' => $val->aprroved == null ? false : true,
                'route' => $itineraryDate && $itineraryDate->route->route_name ? $itineraryDate->route->route_name : "",
                'dayType' => $dayTypes ? $dayTypes->dayType->dt_name : '',
                'def_actual_mileage' => $mileage ? round($mileage, 2) : 0,
                'actual_mileage' => isset($val->actual_mileage) ? round($val->actual_mileage, 2) : round($mileage, 2),
                // 'mileage_amount' => isset($val->mileage_amount)?round($val->mileage_amount,2):isset($val->actual_mileage) && !isset($val->mileage_amount)?round($val->actual_mileage*$rate,2):0,
                'mileage_amount' => isset($val->actual_mileage) ? round($val->actual_mileage * $rate, 2) : !isset($val->actual_mileage) && isset($mileage) ? round($mileage * $rate, 2) : 0,
                'vht_rate' => $rate ? $rate : 0,
            ];
        });

        return [
            'results' => $expenses,
        ];
    }

    public function saveEditWExpenses(Request $request)
    {
        $expenses = $request->input('expenses');

        $user = Auth::user();
        foreach ($expenses as $key => $val) {
            $exp = SfaExpenses::where('sfa_exp_id', $val['exp_id'])->first();
            $exp->bt_id = $val['bataType']['value'];
            $exp->mileage = $val['mileage'];
            $exp->parking = $val['parking'];
            $exp->remark = $val['remark'];
            $exp->stationery = $val['stationery'];
            $exp->aprroved = $val['status'] == true ? date('Y-m-d H:i:s') : null;
            $exp->approved_u_id = $val['status'] == true ? $user->getKey() : null;
            $exp->def_actual_mileage = $val['def_actual_mileage'];
            $exp->actual_mileage = $val['actual_mileage'];
            $exp->mileage_amount = $val['mileage_amount'];
            $exp->save();
        }

        return [
            'message' => "Expenses Data has been Updated",
        ];
    }

    public function saveAsmExp(Request $request)
    {
        $bata = $request->input('values.bata.value');
        $mileage = $request->input('values.mileage');
        $parking = $request->input('values.parking');
        $remark = $request->input('values.remark');
        $stationary = $request->input('values.stationary');

        $validator = Validator::make($request->all(), [
            'values.bata.value' => 'required',
        ]);

        if ($validator->fails()) {
            throw new WebAPIException("Bata Type is required");
        }

        $user = Auth::user();

        $expenses = SfaExpenses::create([
            'bt_id' => $bata,
            'stationery' => $stationary ? $stationary : 0,
            'parking' => $parking ? $parking : 0,
            'remark' => $remark ? $remark : '',
            'app_version' => null,
            'exp_time' => date('Y-m-d H:i:s'),
            'u_id' => $user->getKey(),
            'mileage' => $mileage ? $mileage : 0,
        ]);

        return response()->json([
            'result' => true,
            'message' => "Asm Expenses Successfully Saved!!!",
        ]);
    }

    public function getUser(Request $request)
    {
        $user = Auth::user();

        return [
            'id' => $user->getKey(),
            'name' => $user->getName(),
            'roll' => $user->getRoll(),
            'picture' => $user->getProfilePicture(),
            'time' => date('Y-m-d H:i:s'),
            'code' => $user->u_code,
        ];
    }

    public function getUserGpsMilage($months, $date, $userId)
    {
        $fromDateTime = date('Y-m-d 00:00:00', strtotime($date));
        $toDateTime = date('Y-m-d 23:59:59', strtotime($date));

        if (count($months) == 1 && $months[0] == date('Y_m')) {
            $coordinates = GPSTracking::where('u_id', $userId)->whereBetween('gt_time', [$fromDateTime, $toDateTime])->get();

        } else {
            $coordinates = collect([]);

            $coordArray = [];

            if (end($months) == date('Y_m')) {
                $coordArray[] = GPSTracking::where('u_id', $userId)->whereBetween('gt_time', [$fromDateTime, $toDateTime])->get();
                array_pop($months);
            }

            foreach ($months as $month) {
                $coordArray[] = DB::table('gps_tracking_' . $month)->where('u_id', $userId)->whereBetween('gt_time', [$fromDateTime, $toDateTime])->get();
            }

            foreach ($coordArray as $coordinateBatch) {
                $coordinates = $coordinates->merge($coordinateBatch);
            }
        }

        $coordinates->transform(function ($coordinate) {

            return [
                'lng' => $coordinate->gt_lon,
                'lat' => $coordinate->gt_lat,
                'batry' => $coordinate->gt_btry,
                'accurazy' => $coordinate->gt_accu,
                'time' => strtotime($coordinate->gt_time),
            ];
        });

        $productives = SfaSalesOrder::with(['chemist'])->where('u_id', $userId)->whereBetween('order_date', [$fromDateTime, $toDateTime])->get();

        $productives->transform(function ($productive) {

            $name = isset($productive->chemist) ? $productive->chemist->chemist_name : 'Deleted';
            $time = strtotime($productive->order_date);

            return [
                'lng' => round($productive->longitude, 7),
                'lat' => round($productive->latitude, 7),
                'batry' => $productive->btry_lvl,
                'accurazy' => 0,
                'time' => $time,
                'title' => $productive->order_no,
                'description' => $name . ' <br/>@ ' . date("H:i:s", $time),
                'type' => 1,
            ];
        });

        $coordinates = $coordinates->concat($productives);

        $unProductives = SfaUnproductiveVisit::with(['chemist'])->where('u_id', $userId)->whereBetween('unpro_time', [$fromDateTime, $toDateTime])->get();

        $unProductives->transform(function ($unProductive) {

            $name = isset($unProductive->chemist) ? $unProductive->chemist->chemist_name : 'Deleted';

            $time = strtotime($unProductive->unpro_time);

            return [
                'lng' => round($unProductive->longitude, 7),
                'lat' => round($unProductive->latitude, 7),
                'batry' => $unProductive->btry_lvl,
                'accurazy' => 0,
                'time' => $time,
                'title' => $unProductive->un_visit_no,
                'description' => $name . ' <br/>@ ' . date("H:i:s", $time),
                'type' => 0,
            ];
        });

        $coordinates = $coordinates->concat($unProductives);

        $query = UserAttendance::where(function (Builder $query) use ($fromDateTime) {
            $query->orWhereDate('check_in_time', $fromDateTime);
            $query->orWhereDate('check_out_time', $fromDateTime);
        })->where('u_id', $userId)->get();

        $mileage_amount = 0;
        $gps_mileage = 0;

        $checkinTime = null;
        $checkoutTime = null;

        foreach ($query as $checking) {
            if ($checking->check_in_time && !$checkinTime) {
                $checkinTime = strtotime($checking->check_in_time);

                $coordinates->push([
                    'lng' => (string) round($checking->check_in_lon - 0.0000005, 7),
                    'lat' => (string) round($checking->check_in_lat - 0.0000005, 7),
                    'batry' => $checking->check_in_battery,
                    'accurazy' => 0,
                    'time' => $checkinTime - 60,
                ]);

                $coordinates->push([
                    'lng' => (string) round($checking->check_in_lon - 0.0000002, 7),
                    'lat' => (string) round($checking->check_in_lat - 0.0000002, 7),
                    'batry' => $checking->check_in_battery,
                    'accurazy' => 0,
                    'time' => $checkinTime - 30,
                ]);

                $coordinates->push([
                    'lng' => (string) round($checking->check_in_lon, 7),
                    'lat' => (string) round($checking->check_in_lat, 7),
                    'batry' => $checking->check_in_battery,
                    'accurazy' => 0,
                    'time' => $checkinTime,
                    'type' => 2,
                ]);

                $coordinates->push([
                    'lng' => (string) round($checking->check_in_lon + 0.0000002, 7),
                    'lat' => (string) round($checking->check_in_lat + 0.0000002, 7),
                    'batry' => $checking->check_in_battery,
                    'accurazy' => 0,
                    'time' => $checkinTime + 30,
                ]);

                $coordinates->push([
                    'lng' => (string) round($checking->check_in_lon + 0.0000005, 7),
                    'lat' => (string) round($checking->check_in_lat + 0.0000005, 7),
                    'batry' => $checking->check_in_battery,
                    'accurazy' => 0,
                    'time' => $checkinTime + 60,
                ]);
            }

            if ($checking->check_out_time) {
                $checkoutTime = strtotime($checking->check_out_time);

                $coordinates->push([
                    'lng' => (string) round($checking->check_out_lon - 0.0000005, 7),
                    'lat' => (string) round($checking->check_out_lat - 0.0000005, 7),
                    'batry' => $checking->check_out_battery,
                    'accurazy' => 0,
                    'time' => $checkoutTime - 60,
                ]);

                $coordinates->push([
                    'lng' => (string) round($checking->check_out_lon - 0.0000002, 7),
                    'lat' => (string) round($checking->check_out_lat - 0.0000002, 7),
                    'batry' => $checking->check_out_battery,
                    'accurazy' => 0,
                    'time' => $checkoutTime - 30,
                ]);

                $coordinates->push([
                    'lng' => (string) round($checking->check_out_lon, 7),
                    'lat' => (string) round($checking->check_out_lat, 7),
                    'batry' => $checking->check_out_battery,
                    'accurazy' => 0,
                    'time' => $checkoutTime,
                    'type' => 3,
                ]);

                $coordinates->push([
                    'lng' => (string) round($checking->check_out_lon + 0.0000002, 7),
                    'lat' => (string) round($checking->check_out_lat + 0.0000002, 7),
                    'batry' => $checking->check_out_battery,
                    'accurazy' => 0,
                    'time' => $checkoutTime + 30,
                ]);

                $coordinates->push([
                    'lng' => (string) round($checking->check_out_lon + 0.0000005, 7),
                    'lat' => (string) round($checking->check_out_lat + 0.0000005, 7),
                    'batry' => $checking->check_out_battery,
                    'accurazy' => 0,
                    'time' => $checkoutTime + 60,
                ]);
            }
        }

        $coordinates = $coordinates->filter(function ($coordinate) use ($checkinTime, $checkoutTime) {
            return $checkinTime && $coordinate['time'] > $checkinTime - 120 && (!$checkoutTime || $checkoutTime && $coordinate['time'] < $checkoutTime + 120);
        });

        $coordinates = $coordinates->values()->toArray();

        $coordinates = array_sort($coordinates, function ($a, $b) {
            return $a['time'] - $b['time'];
        });

        $coordinates = array_values($coordinates);

        $gps_mileage = $this->calculateDistance($coordinates);

        return $gps_mileage;
    }

    protected function getMonthsBetween($a, $b)
    {

        $i = date("Y_m", strtotime($a));
        while ($i <= date("Y_m", strtotime($b))) {
            $months[] = $i;
            if (substr($i, 4, 2) == "12") {
                $i = (date("Y", strtotime($i . "01")) + 1) . "01";
            } else {
                $i++;
            }

        }

        return $months;
    }

    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {

        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2) + sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $d = $R * $c;

        return $d;
    }

    protected function calculateDistance($coordinates)
    {
        $distanceTotal = 0;

        try {
            foreach ($coordinates as $key => $value) {
                $distanceTotal += $this->distance($coordinates[$key]['lat'], $coordinates[$key]['lng'], $coordinates[$key + 1]['lat'], $coordinates[$key + 1]['lng'], "K");
            }
        } catch (\Exception $e) {

        }

        return json_encode($distanceTotal);
    }
}
