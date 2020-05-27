<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\BataType;
use App\Models\SfaExpenses;
use App\Models\User;
use App\Models\VehicleTypeRate;
use Illuminate\Http\Request;

class ExpensesSaleStatementSummuryReportController extends ReportController
{

    protected $title = "Expenses Statement Summury Report";

    public function search($request)
    {
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        $query = User::with('vehicle_type');
        $query->where('u_tp_id', 10);

        if (isset($values['user'])) {
            $query->where('id', $values['user']['value']);
        }

        $count = $this->paginateAndCount($query, $request, 'id');
        $results = $query->get();

        $results->transform(function ($val) use ($values) {

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
            $return['gps_mileage'] = 0;

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
