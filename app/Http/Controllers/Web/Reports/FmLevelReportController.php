<?php
namespace App\Http\Controllers\Web\Reports;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\StationMileage;
use App\Models\SubTown;
use App\Models\User;
use App\Models\VehicleTypeRate;
use App\Traits\Territory;
use Illuminate\Http\Request;

class FmLevelReportController extends Controller
{

    use Territory;
    //  use User;

    public function __searchBy($values)
    {

        $jfw = "";
        $itineraries = [];
        // return $values['team']['value'];

        if (!isset($values['user']) || !isset($values['user']['value'])) {
            throw new WebAPIException("Field Manager field is required");
        }

        $userId = User::find($values['user']['value']);

        $users = User::getByUser($userId);
        $jfwUsers = $users->map(function ($jfw) {
            return [
                "name" => $jfw->name,
                "id" => $jfw->id,
            ];
        });

        $begin = new \DateTime(date('Y-m-01', strtotime($values['e_date'])));
        $end = new \DateTime(date("Y-m-t", strtotime($values['e_date'])));
        $end = $end->modify('1 day');

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($begin, $interval, $end);

        // Seperate the year and month
        $year = date('Y', strtotime($values['e_date']));
        $month = date('m', strtotime($values['e_date']));

        $itineraries[] = Itinerary::where(function ($query) use ($userId) {
            // $query->orWhere('rep_id', $userId->getKey());
            $query->orWhere('fm_id', $userId->getKey());
        })
            ->whereNotNull('i_aprvd_at')
            ->where('i_year', $year)
            ->where('i_month', $month)
            ->latest()
            ->first();

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
            'itinerary',
        ];

        $itineraryDates = ItineraryDate::with($itineraryRelations)
            ->whereIn('i_id', $itineraries->pluck('i_id')->all())
            ->get();

        $itineraryDates->transform(function (ItineraryDate $itineraryDate) use ($itineraries, $itineraryRelations, $userId) {

            $formatedDate = $itineraryDate->getFormatedDetails();

            $mileage = $formatedDate->getMileage();
            $bataType = $formatedDate->getBataType();
            $towns = $formatedDate->getSubTowns();
            $types = $formatedDate->getDayTypes();

            // Generating the date
            $itinerary = $itineraries->where('i_id', $itineraryDate->i_id)->first();
            $date = $itinerary->i_year . "-" . str_pad($itinerary->i_month, 2, "0", STR_PAD_LEFT) . '-' . str_pad($itineraryDate->id_date, 2, "0", STR_PAD_LEFT);

            // Vehicle type rate retrieving
            $vehicleTypeRateInst = VehicleTypeRate::where('vht_id', $userId->vht_id)->whereDate('vhtr_srt_date', '<=', $date)->where('u_tp_id', $userId->u_tp_id)->latest()->first();
            $vehicleTypeRate = $vehicleTypeRateInst ? $vehicleTypeRateInst->vhtr_rate : 0;

            // If join field worker selected areas picking by his itinerary
            if (isset($itineraryDate->joinFieldWorker)) {
                $jfwItineraryDate = ItineraryDate::getTodayForUser($itineraryDate->joinFieldWorker, $itineraryRelations, strtotime($date));
                $jfwFormatedDate = $jfwItineraryDate->getFormatedDetails();
                $towns = $jfwFormatedDate->getSubTowns();
                $jfw = $itineraryDate->joinFieldWorker->name;
            }

            // Formating town names
            $townNames = $towns->map(function (SubTown $subTown) {
                return $subTown->sub_twn_name;
            })->all();
            $townNames = implode(', ', $townNames);

            $typeNames = implode(', ', array_map(function ($dayType) {
                return $dayType->dt_name;
            }, $types));

            return [
                "mileage" => $mileage,
                "date" => $date,
                'townNames' => $townNames,
                'bataType' => $bataType,
                'itineraryId' => $itineraryDate->i_id,
                'rate' => $vehicleTypeRate,
                'dateType' => $formatedDate->getDateType(),
                'isWorking' => $formatedDate->getWorkingDay(),
                'isFieldWorking' => $formatedDate->getFieldWorkingDay(),
                'typeNames' => $typeNames,
                'jfw' => isset($jfw) ? $jfw : '-',
            ];
        });

        // return $itineraryDates;

        $rows = [];

        foreach ($period as $dt) {
            $row = [];
            $date = $dt->format("Y-m-d");

            // date
            $row[] = $date;

            $itineraryDate = $itineraryDates->where('date', $date)->first();
            $bataCategory = $itineraryDate['bataType'] ? $itineraryDate['bataType']->bataCategory : null;

            // date type
            $row[] = isset($itineraryDate['typeNames']) ? $itineraryDate['typeNames'] : '-';
            // mr
            $row[] = isset($itineraryDate['jfw']) ? $itineraryDate['jfw'] : "-";
            // town
            $row[] = isset($itineraryDate['townNames']) ? $itineraryDate['townNames'] : "-";
            // mileage
            $row[] = isset($itineraryDate['mileage']) ? round($itineraryDate['mileage'], 2) : 0;
            // station mileage
            $row[] = isset($bataCategory) ? $bataCategory->btc_category : "-";
            $rows[] = $row;
        }

        return [
            'count' => 0,
            'results' => $rows,
            'jfw' => $jfwUsers,
        ];
    }

    public function search(Request $request)
    {
        $values = $request->input('values', []);

        return $this->__searchBy($values);
    }
}
