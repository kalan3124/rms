<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Models\StandardItinerary;
use App\Models\StandardItineraryDate;
use App\Models\StandardItineraryDateArea;
use Illuminate\Http\Request;
use Validator;
use App\Http\Controllers\Controller;
use App\Models\StandardItineraryDateCustomer;
use Illuminate\Database\Eloquent\Collection;

class StandardItineraryController extends Controller
{
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rep' => 'required|array',
            'rep.value' => 'required|numeric|exists:users,id',
            'dates' => 'required|array',
            'dates.*.date_number' => 'required|between:0,32',
            'dates.*.mileage' => 'numeric',
            'dates.*.description' => 'required',
            'dates.*.areas' => 'required|array',
            'dates.*.bata' => 'array',
            'dates.*.bata.value' => 'numeric|exists:bata_type,bt_id',
        ]);

        if ($validator->fails()) {
            throw new WebAPIException($validator->errors()->first());
        }

        $rep = $request->input('rep');

        $itinerary = StandardItinerary::create([
            'u_id' => $rep['value'],
        ]);

        $dates = $request->input('dates');

        foreach ($dates as $date => $details) {

            $itineraryDate = StandardItineraryDate::create([
                'si_id' => $itinerary->getKey(),
                'bt_id' => isset($details['bata'])?$details['bata']['value']:null,
                'sid_mileage' => $details['mileage'],
                'sid_date' => $details['date_number'],
                'sid_description' => $details['description'],
            ]);

            foreach ($details['areas'] as $area) {
                StandardItineraryDateArea::create([
                    'sid_id' => $itineraryDate->getKey(),
                    'sub_twn_id' => $area['value']
                ]);
            }

            if(isset($details['chemists'])&&is_array($details['chemists'])){
                foreach ($details['chemists'] as $key => $chemist) {
                    StandardItineraryDateCustomer::create([
                        'sid_id' => $itineraryDate->getKey(),
                        'chemist_id'=>$chemist['value']
                    ]);
                }
            }

            if(isset($details['doctors'])&&is_array($details['doctors'])){
                foreach ($details['doctors'] as $key => $doctor) {
                    StandardItineraryDateCustomer::create([
                        'sid_id' => $itineraryDate->getKey(),
                        'doc_id'=>$doctor['value']
                    ]);
                }
            }

            if(isset($details['otherHospitalStaffs'])&&is_array($details['otherHospitalStaffs'])){
                foreach ($details['otherHospitalStaffs'] as $key => $otherHospitalStaff) {
                    StandardItineraryDateCustomer::create([
                        'sid_id' => $itineraryDate->getKey(),
                        'hos_stf_id'=>$otherHospitalStaff['value']
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully saved your standard itinerary!",
        ]);
    }

    public function load(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rep' => 'required|array',
            'rep.value' => 'required|numeric|exists:users,id',
        ]);

        if ($validator->fails()) {
            throw new WebAPIException("Please provide a medical representative to search.");
        }

        $rep = $request->input('rep');

        $itinerary = StandardItinerary::with(['standardItineraryDates','standardItineraryDates.bataType'])->where('u_id', $rep['value'])->latest()->first();

// Aborting if itinerary not found
        if (!$itinerary) {
            throw new WebAPIException("Can not find a standard itinerary for the given user!");
        }

// Formating the itinerary dates
        $itinerary->standardItineraryDates->transform(function ($date) {

            // Getting the selected territories for the itinerary date
            $areas = StandardItineraryDateArea::where('sid_id', $date->getKey())->with([
                'sub_town','sub_town.town'
            ])->get();

            /** @var StandardItineraryDateCustomer[]|Collection $customers */
            $customers = StandardItineraryDateCustomer::where('sid_id',$date->getKey())->with([
                'chemist','doctor','otherHospitalStaff'
            ])->get();

            // Formating the territories
            $formatedAreas = $areas->map(function ($area) {
                    if(!isset($area->sub_town)){
                        return null;
                    }

                    return [
                        'label'=>$area->sub_town->sub_twn_name,
                        'value'=>$area->sub_town->getKey()
                    ];
            })
            ->filter(function($area){return !!$area;})
            ->values();

            $bata = null;
            if($date->bataType){
                $bata = [
                    'label'=>$date->bataType->bt_name,
                    'value'=>$date->bataType->getKey()
                ];
            }

            // Returning
            return [
                'date_number' => $date->sid_date,
                'bata' => $bata,
                'mileage' => $date->sid_mileage,
                'areas' => $formatedAreas,
                'description'=>$date->sid_description,
                'id'=>$date->getKey(),
                'chemists'=>$customers->filter(function(StandardItineraryDateCustomer $standardItineraryDateCustomer){
                    return !!$standardItineraryDateCustomer->chemist;
                })->map(function(StandardItineraryDateCustomer $standardItineraryDateCustomer){
                    return [
                        'value'=>$standardItineraryDateCustomer->chemist_id,
                        'label'=>$standardItineraryDateCustomer->chemist->chemist_name
                    ];
                })->values(),
                'doctors'=>$customers->filter(function(StandardItineraryDateCustomer $standardItineraryDateCustomer){
                    return !!$standardItineraryDateCustomer->doctor;
                })->map(function(StandardItineraryDateCustomer $standardItineraryDateCustomer){
                    return [
                        'value'=>$standardItineraryDateCustomer->doc_id,
                        'label'=>$standardItineraryDateCustomer->doctor->doc_name
                    ];
                })->values(),
                'otherHospitalStaffs'=>$customers->filter(function(StandardItineraryDateCustomer $standardItineraryDateCustomer){
                    return !!$standardItineraryDateCustomer->otherHospitalStaff;
                })->map(function(StandardItineraryDateCustomer $standardItineraryDateCustomer){
                    return [
                        'value'=>$standardItineraryDateCustomer->otherHospitalStaff->getKey(),
                        'label'=>$standardItineraryDateCustomer->otherHospitalStaff->hos_stf_name
                    ];
                })->values(),
            ];
        });

        $datesUnformated = $itinerary->standardItineraryDates->toArray();

        $datesFormated = [];

        foreach ($datesUnformated as $key => $date) {
            $datesFormated[$date['date_number']] = $date;
        }

        return $datesFormated;
    }
}
