<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\BataType;
use App\Models\GPSTracking;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SfaExpenses;
use App\Models\SfaSalesOrder;
use App\Models\SfaUnproductiveVisit;
use App\Models\User;
use App\Models\UserAttendance;
use App\Models\VehicleTypeRate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpensesSaleStatementReportController extends ReportController
{

    protected $title = "Expenses Statement Report";

    public function search($request)
    {
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        if (!isset($values['user'])) {
            throw new WebAPIException("User field is required!");
        }

        $user = User::with('vehicle_type')->where('id', $values['user']['value'])->first();

        $start = new \DateTime($values['s_date']);
        $end = new \DateTime($values['e_date']);
        $end = $end->modify('1 day');

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end);

        $fromDate = date('Y-m-d 00:00:00', strtotime($values['s_date']));
        $toDate = date('Y-m-d 23:59:59', strtotime($values['e_date']));

        $months = $this->getMonthsBetween($fromDate, $toDate);

        $formattedResults = [];
        $mileage_tot = 0;
        $exp_tot = 0;

        $grand_mileage = 0;
        $grand_mileage_pay = 0;
        $grand_ad_mileage = 0;
        $grand_tot_mile = 0;

        $grand_parking = 0;
        $grand_stationary = 0;
        $grand_exp = 0;

        $grand_tot = 0;
        foreach ($period as $key => $dt) {

            // New Gps Calculations Start

            $fromDateTime = date('Y-m-d 00:00:00', strtotime($dt->format('Y-m-d')));
            $toDateTime = date('Y-m-d 23:59:59', strtotime($dt->format('Y-m-d')));

            if (count($months) == 1 && $months[0] == date('Y_m')) {
                $coordinates = GPSTracking::where('u_id', $user->id)->whereBetween('gt_time', [$fromDateTime, $toDateTime])->get();

            } else {
                $coordinates = collect([]);

                $coordArray = [];

                if (end($months) == date('Y_m')) {
                    $coordArray[] = GPSTracking::where('u_id', $user->id)->whereBetween('gt_time', [$fromDateTime, $toDateTime])->get();
                    array_pop($months);
                }

                foreach ($months as $month) {
                    $coordArray[] = DB::table('gps_tracking_' . $month)->where('u_id', $user->id)->whereBetween('gt_time', [$fromDateTime, $toDateTime])->get();
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

            $productives = SfaSalesOrder::with(['chemist'])->where('u_id', $user->id)->whereBetween('order_date', [$fromDateTime, $toDateTime])->get();

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

            $unProductives = SfaUnproductiveVisit::with(['chemist'])->where('u_id', $user->id)->whereBetween('unpro_time', [$fromDateTime, $toDateTime])->get();

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
            })->where('u_id', $user->id)->get();

            $mileage_amount = 0;
            $gps_mileage = 0;

            $checkinTime = null;
            $checkoutTime = null;

            foreach ($checkings as $key => $checking) {

                $attDate = date("Y-m-d", strtotime($checking->check_in_time));
                $providedDate = date("Y-m-d", strtotime($fromDateTime));

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

            $exp = SfaExpenses::where('u_id', $values['user']['value'])->whereDate('exp_time', $dt->format('Y-m-d'))->first();

            $rate = 0;
            if (isset($user->vehicle_type)) {
                $vhType = VehicleTypeRate::where('vht_id', $user->vehicle_type->vht_id)->where('u_tp_id', 10)->latest()->first();
            }

            if (isset($vhType)) {
                $rate = $vhType->vhtr_rate;
            }

            $itinerary = SalesItinerary::where('s_i_year', $values['s_date'])
                ->where('s_i_month', $dt->format('m'))
                ->where('u_id', $values['user']['value'])
                ->latest()
                ->first();

            $itineraryDate = SalesItineraryDate::with('route', 'bataType')
                ->where('s_i_id', $itinerary['s_i_id'])
                ->where('s_id_date', $dt->format('d'))
                ->first();

            $return['date'] = $dt->format('Y-m-d');
            $return['date_style'] = [
                'color' => 'black',
                'background' => '#e4e8f0',
                'border' => '1px solid #fff',
            ];

            $town = "";
            if (isset($itineraryDate->route)) {
                $town = $itineraryDate->route->route_name;
            }

            if ($dt->format('D') == "Sat") {
                $town = "Saturday";
            } else if ($dt->format('D') == "Sun") {
                $town = "Sunday";
            }

            $return['town'] = $town;
            $return['town_style'] = [
                'color' => 'black',
                'background' => '#e4e8f0',
                'border' => '1px solid #fff',
            ];
            $return['station'] = isset($itineraryDate->bataType) ? $itineraryDate->bataType->bt_name : '-';
            $return['km'] = 0;
            $return['base_allo'] = 0;

            $bata_tot = 0;
            $bata_tot_sum = 0;
            $bataCatTypes = BataType::where('bt_type', 3)->get();
            foreach ($bataCatTypes as $key => $bataCatType) {
                $exp_bata = SfaExpenses::with('bataType')->where('u_id', $values['user']['value'])->where('bt_id', $bataCatType->getKey())->whereDate('exp_time', $dt->format('Y-m-d'))->first();
                $bata_tot = isset($exp_bata->bataType) ? $exp_bata->bataType->bt_value : 0;
                $return['bt_id_' . $bataCatType->getKey()] = $bata_tot;
                $bata_tot_sum += $bata_tot;
            }
            $return['bata_tot'] = $bata_tot_sum;

            $return['bata_tot_style'] = [
                'color' => 'black',
                'background' => '#e4e8f0',
                'border' => '1px solid #fff',
            ];

            $return['mileage'] = isset($exp['mileage']) ? $exp['mileage'] : 0;

            $return['pay_mileage'] = isset($exp['mileage']) ? $exp['mileage'] * $rate : 0;
            $return['ad_mileage'] = 0;
            $return['pvt_mileage'] = 0;
            $return['gps_mileage'] = number_format($gps_mileage, 2);

            $mileage_tot = ($exp['mileage'] * $rate) + $exp['mileage'];
            $return['mileage_tot'] = $mileage_tot;
            $return['mileage_tot_style'] = [
                'color' => 'black',
                'background' => '#e4e8f0',
                'border' => '1px solid #fff',
            ];

            $return['parking'] = isset($exp['parking']) ? $exp['parking'] : 0;
            $return['stationery'] = isset($exp['stationery']) ? $exp['stationery'] : 0;

            $exp_tot = $exp['parking'] + $exp['stationery'];
            $return['exp_tot'] = $exp_tot;
            $return['exp_tot_style'] = [
                'color' => 'black',
                'background' => '#e4e8f0',
                'border' => '1px solid #fff',
            ];

            $return['grnd_tot'] = $bata_tot + $mileage_tot + $exp_tot;
            $return['grnd_tot_style'] = [
                'color' => 'black',
                'background' => '#d3d8e3',
                'border' => '1px solid #fff',
            ];

            $formattedResults[] = $return;

            $grand_mileage += $exp['mileage'];
            $grand_mileage_pay += ($exp['mileage'] * $rate);
            $grand_ad_mileage = 0;
            $grand_tot_mile += $mileage_tot;
            $grand_parking += $exp['parking'];
            $grand_stationary += $exp['stationery'];
            $grand_exp += $exp_tot;
            $grand_tot += $bata_tot_sum + $mileage_tot + $exp_tot;
        }

        $row = [
            'special' => true,
            'date' => 'Grand Total',
            'town' => '',
            'station' => '',
            'km' => 0,
            'base_allo' => isset($user->base_allowances) ? number_format($user->base_allowances, 2) : 0,
            'mileage' => number_format($grand_mileage, 2),
            'pay_mileage' => number_format($grand_mileage_pay, 2),
            'ad_mileage' => number_format($grand_ad_mileage, 2),
            'pvt_mileage' => 0,
            'gps_mileage' => 0,
            'mileage_tot' => number_format($grand_tot_mile, 2),
            'parking' => number_format($grand_parking, 2),
            'stationery' => number_format($grand_stationary, 2),
            'exp_tot' => number_format($grand_exp, 2),
            'grnd_tot' => number_format($grand_tot, 2),
        ];

        // $expRow = [

        // ];

        $formattedResults[] = $row;
        return [
            'results' => $formattedResults,
            'count' => 0,
        ];
    }

    protected function getAdditionalHeaders($request)
    {
        $bataCatCount = BataType::where('bt_type', 3)->count();
        // $expencesCount = Reason::latest()->where('rsn_type', config('shl.expenses_reason_type'))->where('rsn_id', '!=', 14)->count();

        $columns = [[
            [
                "title" => "",
                "colSpan" => 5,
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
        $columnController->text('date')->setLabel('Date')->setSearchable(false);
        $columnController->text('town')->setLabel('Town')->setSearchable(false);
        $columnController->text('station')->setLabel('Station')->setSearchable(false);
        $columnController->number('km')->setLabel('Km(s)')->setSearchable(false);
        $columnController->number('base_allo')->setLabel('Base Allowance')->setSearchable(false);

        $bataCatTypes = BataType::where('bt_type', 3)->get();
        foreach ($bataCatTypes as $key => $bataCatType) {
            $columnController->number('bt_id_' . $bataCatType->getKey())->setLabel($bataCatType->bt_name)->setSearchable(false);
        }
        $columnController->number('bata_tot')->setLabel("Total")->setSearchable(false);

        $columnController->number('mileage')->setLabel(" Mileage")->setSearchable(false);
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
}
