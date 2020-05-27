<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\BataType;
use App\Models\GPSTracking;
use App\Models\SfaExpenses;
use App\Models\SfaSalesOrder;
use App\Models\SfaUnproductiveVisit;
use App\Models\User;
use App\Models\UserAttendance;
use App\Models\VehicleTypeRate;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ExpensesSalesStatementSummuryReportController extends ReportController
{

    protected $title = "SR Expenses Statement Summury Report";

    public function search($request)
    {
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        $fromDateTime = date('Y-m-d 00:00:00', strtotime($values['e_date']));
        $toDateTime = date('Y-m-d 23:59:59', strtotime($values['s_date']));

        $months = $this->getMonthsBetween($fromDateTime, $toDateTime);

        $query = User::with('vehicle_type');
        $query->where('u_tp_id', 10);

        if (isset($values['user'])) {
            $query->where('id', $values['user']['value']);
        }

        $count = $this->paginateAndCount($query, $request, 'id');
        $results = $query->get();

        $results->transform(function ($val) use ($values,$fromDateTime, $toDateTime,$months) {

            if (count($months) == 1 && $months[0] == date('Y_m')) {
                $coordinates = GPSTracking::where('u_id', $val->id)->whereBetween('gt_time', [$fromDateTime, $toDateTime])->get();

            } else {
                $coordinates = collect([]);

                $coordArray = [];

                if (end($months) == date('Y_m')) {
                    $coordArray[] = GPSTracking::where('u_id', $val->id)->whereBetween('gt_time', [$fromDateTime, $toDateTime])->get();
                    array_pop($months);
                }

                foreach ($months as $month) {
                    $coordArray[] = DB::table('gps_tracking_' . $month)->where('u_id', $val->id)->whereBetween('gt_time', [$fromDateTime, $toDateTime])->get();
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

            $productives = SfaSalesOrder::with(['chemist'])->where('u_id', $val->id)->whereBetween('order_date', [$fromDateTime, $toDateTime])->get();

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

            $unProductives = SfaUnproductiveVisit::with(['chemist'])->where('u_id', $val->id)->whereBetween('unpro_time', [$fromDateTime, $toDateTime])->get();

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

            $checkings = UserAttendance::where(function (Builder $query) use ($fromDateTime) {
                $query->orWhereDate('check_in_time', $fromDateTime);
                $query->orWhereDate('check_out_time', $fromDateTime);
            })->where('u_id', $val->id)->get();


            $mileage_amount = 0;
            $gps_mileage = 0;

            $checkinTime = null;
            $checkoutTime = null;

            foreach ($checkings as $key => $checking) {

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
                        'title' => "Checkin",
                        'description' => "@ " . date('H:i:s', $checkinTime),
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

            if (isset($checking)) {
                $coordinates->push([
                    'lng' => (string) round($checking->check_out_lon, 7),
                    'lat' => (string) round($checking->check_out_lat, 7),
                    'batry' => $checking->check_out_battery,
                    'accurazy' => 0,
                    'time' => $checkoutTime,
                    'title' => "Checkout",
                    'description' => "@ " . date('H:i:s', $checkoutTime),
                    'type' => 3,
                ]);
            }

            $coordinates = $coordinates->filter(function ($coordinate) use ($checkinTime, $checkoutTime) {
                return $checkinTime && $checkoutTime && $coordinate['time'] > $checkinTime - 120 && $coordinate['time'] < $checkoutTime + 120;
                // return $checkinTime && $coordinate['time'] > $checkinTime - 120 && (!$checkoutTime || $checkoutTime && $coordinate['time'] < $checkoutTime + 120);
            });

            $coordinates = $coordinates->values()->toArray();

            $coordinates = array_sort($coordinates, function ($a, $b) {
                return $a['time'] - $b['time'];
            });

            $coordinates = array_values($coordinates);

            $gps_mileage = $this->calculateDistance($coordinates);


            $exp = SfaExpenses::where('u_id', $val->id)->whereDate('exp_time', '>=', $values['s_date'])->whereDate('exp_time', '<=', $values['e_date'])->sum('mileage');
            $expParking = SfaExpenses::where('u_id', $val->id)->whereDate('exp_time', '>=', $values['s_date'])->whereDate('exp_time', '<=', $values['e_date'])->sum('parking');
            $expSationary = SfaExpenses::where('u_id', $val->id)->whereDate('exp_time', '>=', $values['s_date'])->whereDate('exp_time', '<=', $values['e_date'])->sum('stationery');

            $rate = 0;
            if (isset($val->vehicle_type)) {
                $vhType = VehicleTypeRate::where('vht_id', $val->vehicle_type->vht_id)->where('u_tp_id', 10)->latest()->first();
            }

            if (isset($vhType)) {
                $rate = $vhType->vhtr_rate;
            }

            $return['code'] = $val->u_code ? $val->u_code : '';
            $return['user'] = $val->name;

            $bataCatTypes = BataType::where('bt_type', 3)->get();
            $bata_tots = 0;
            foreach ($bataCatTypes as $key => $bataCatType) {
                $exp_bata = SfaExpenses::with('bataType')->where('u_id', $val->id)->where('bt_id', $bataCatType->getKey())->whereDate('exp_time', '>=', $values['s_date'])->whereDate('exp_time', '<=', $values['e_date'])->get();
                $bata_tot = 0;
                foreach ($exp_bata as $key => $val) {
                    $bata_tot += isset($val->bataType) ? $val->bataType->bt_value : 0;
                }

                $return['bt_id_' . $bataCatType->getKey()] = $bata_tot;
                $bata_tots += $bata_tot;
            }
            $return['bata_tot'] = $bata_tots;

            $return['mileage'] = isset($exp) ? $exp : 0;
            $return['pay_mileage'] = isset($exp) ? $exp * $rate : 0;
            $return['ad_mileage'] = 0;
            $return['pvt_mileage'] = 0;
            $return['gps_mileage'] = isset($gps_mileage)?round($gps_mileage,2):0;

            $return['mileage_tot'] = $exp + ($exp * $rate);

            $return['parking'] = isset($expParking) ? $expParking : 0;
            $return['stationery'] = isset($expSationary) ? $expSationary : 0;

            $return['exp_tot'] = $expParking + $expSationary;

            $return['grnd_tot'] = $bata_tots + ($exp + ($exp * $rate)) + ($expParking + $expSationary);

            return $return;
        });

        $row = [
            'special' => true,
            'code' => 'Total',
            'user' => NULL,
            'mileage' => number_format($results->sum('mileage'),2),
            'pay_mileage' => number_format($results->sum('pay_mileage'),2),
            'ad_mileage' => number_format($results->sum('ad_mileage'),2),
            'pvt_mileage' => number_format($results->sum('pvt_mileage'),2),
            'gps_mileage' => number_format($results->sum('gps_mileage'),2),
            'mileage_tot' => number_format($results->sum('mileage_tot'),2),
            'parking' => number_format($results->sum('parking'),2),
            'stationery' => number_format($results->sum('stationery'),2),
            'exp_tot' => number_format($results->sum('exp_tot'),2),
            'grnd_tot' => number_format($results->sum('grnd_tot'),2),
        ];

        $results->push($row);

        return [
            'results' => $results,
            'count' => $count,
        ];
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

    protected function getAdditionalHeaders($request)
    {
        $bataCatCount = BataType::where('bt_type', 3)->count();

        $columns = [[
            [
                "title" => "",
                "colSpan" => 2,
            ],
            [
                "title" => "Bata (Rs)",
                "colSpan" => $bataCatCount + 1,
            ],
            [
                "title" => "Mileage pay (Km)",
                "colSpan" => 6,
            ],
            [
                "title" => "Other Types",
                "colSpan" => 3,
            ],
            [
                "title" => "",
            ],
        ]];

        return $columns;
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('code')->setLabel('Code')->setSearchable(false);
        $columnController->text('user')->setLabel('SR')->setSearchable(false);

        $bataCatTypes = BataType::where('bt_type', 3)->get();
        foreach ($bataCatTypes as $key => $bataCatType) {
            $columnController->number('bt_id_' . $bataCatType->getKey())->setLabel($bataCatType->bt_name)->setSearchable(false);
        }
        $columnController->number('bata_tot')->setLabel("Total")->setSearchable(false);

        $columnController->number('mileage')->setLabel("Mileage")->setSearchable(false);
        $columnController->number('pay_mileage')->setLabel("Mileage Pay")->setSearchable(false);
        $columnController->number('ad_mileage')->setLabel("Additional Mileage")->setSearchable(false);
        $columnController->number('pvt_mileage')->setLabel("Private Mileage")->setSearchable(false);
        $columnController->number('gps_mileage')->setLabel("GPS Mileage")->setSearchable(false);
        $columnController->number('mileage_tot')->setLabel("Total")->setSearchable(false);

        $columnController->number('parking')->setLabel("Parking")->setSearchable(false);
        $columnController->number('stationery')->setLabel("Stationery")->setSearchable(false);

        $columnController->number('exp_tot')->setLabel("Total")->setSearchable(false);
        $columnController->number('grnd_tot')->setLabel("Grand Total")->setSearchable(false);
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type')])->setValidations('');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');

        $inputController->setStructure([
            ['user', 's_date', 'e_date'],
        ]);
    }
}
