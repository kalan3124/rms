<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\Itinerary;
use App\Models\SpecialDay;
use App\Models\ItineraryDate;
use App\Models\DayType;
use App\Models\SubTown;

class ItineraryViewerController extends Controller{
    public function search(Request $request){
        $validation = Validator::make($request->all(),[
            "user"=>"required|array",
            "user.value"=>"required|exists:users,id",
            'month'=>'date'
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $perPage = $request->input("perPage",20);

        $count = $this->makeQuery($request)->count();

        $itineraries = $this->makeQuery($request)
            ->with(['approver','fieldManager','medicalRep'])
            ->take($perPage)
            ->latest()
            ->skip($perPage*($request->input('page',1)-1))
            ->get();
        
        $itineraries->transform(function($itinerary){

            $user = null;

            if(isset($itinerary->medicalRep)){
                $user = [
                    "label"=>$itinerary->medicalRep->name,
                    "value"=>$itinerary->medicalRep->getKey()
                ];
            } else if(isset($itinerary->fieldManager)) {
                $user= [
                    "lable"=>$itinerary->fieldManager->name,
                    "value"=>$itinerary->fieldManager->getKey()
                ];
            }

            $approver = null;

            if(isset($itinerary->approver)){
                $approver = [
                    "label"=>$itinerary->approver->name,
                    "value"=>$itinerary->approver->getKey()
                ];
            }

            return [
                "id"=>$itinerary->getKey(),
                "year"=>$itinerary->i_year,
                "month"=>$itinerary->i_month,
                "user"=>$user,
                "approver"=>$approver,
                "approvedTime"=>$itinerary->i_aprvd_at,
                "createdTime"=>$itinerary->created_at->format("Y-m-d H:i:s")
            ];
        });

        return response()->json([
            'success'=>true,
            "count"=>$count,
            "itineraries"=>$itineraries
        ]);
        
    }

    public function makeQuery( Request $request){

        $itinerariesQuery = Itinerary::where(function($query)use($request){
            $query->orWhere('rep_id',$request->input('user.value'));
            $query->orWhere('fm_id',$request->input('user.value'));
        });

        if($request->has('month')){
            $year = date('Y',strtotime($request->input('month')));
            $month = date('m',strtotime($request->input('month')));

            $itinerariesQuery->where('i_year',$year);
            $itinerariesQuery->where('i_month',$month);

        }

        return $itinerariesQuery;
    }

    public function loadItinerary(Request $request){
        $validation = Validator::make($request->all(),[
            'id'=>'required|numeric'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request!");
        }

         // Finding the itinerary
         $query = Itinerary::where([
                 'i_id' => $request->input('id'),
             ])
             ->latest()
             ->with([
                'itineraryDates',
                'itineraryDates.joinFieldWorker',
                'itineraryDates.standardItineraryDate',
                'itineraryDates.standardItineraryDate.bataType',
                'itineraryDates.standardItineraryDate.bataType.bataCategory',
                'itineraryDates.additionalRoutePlan',
                'itineraryDates.additionalRoutePlan.bataType',
                'itineraryDates.additionalRoutePlan.bataType.bataCategory',
                'itineraryDates.changedItineraryDate',
                'itineraryDates.changedItineraryDate.bataType',
                'itineraryDates.changedItineraryDate.bataType.bataCategory',
                'itineraryDates.bataType',
                'itineraryDates.bataType.bataCategory',
             ]);
 
         $itinerary = $query->first();

         $year = $itinerary->i_year;
         $month = $itinerary->i_month;
 
         // Aborting if itinerary not found
         if (!$itinerary) {
             abort(404);
         }
 
         $specialDays = SpecialDay::whereYear('sd_date',$year)->whereMonth('sd_date',$month)->get();
 
         $specialDays->transform(function($specialDay){
             return [
                 'date'=>date('Y-m-d',strtotime($specialDay->sd_date)),
                 'description'=>$specialDay->sd_name,
                 "types"=>[]
             ];
         });
 
         // Formating the itinerary dates
         $itinerary->itineraryDates->transform(function ( ItineraryDate $itineraryDate)use($year,$month,$specialDays) {
            
            $formatedDate =  $itineraryDate->getFormatedDetails();

            $towns = $formatedDate->getSubTowns();

            $bataType = $formatedDate->getBataType();

            if($bataType){
                $bataType = [
                    'value'=>$bataType->getKey(),
                    'label'=>$bataType->bt_name
                ];
            }

            $date = $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($itineraryDate->id_date,2,'0',STR_PAD_LEFT);

             // If join field worker selected areas picking by his itinerary
             if(isset($itineraryDate->joinFieldWorker)){
                $jfwItineraryDate = ItineraryDate::getTodayForUser($itineraryDate->joinFieldWorker,[
                    'bataType',
                    'joinFieldWorker',
                    'additionalRoutePlan',
                    'additionalRoutePlan.bataType',
                    'additionalRoutePlan.areas',
                    'additionalRoutePlan.areas.subTown',
                    'itineraryDayTypes',
                    'itineraryDayTypes.dayType',
                    'standardItineraryDate',
                    'changedItineraryDate',
                ],strtotime($date));
                $jfwFormatedDate = $jfwItineraryDate->getFormatedDetails();
                $towns = $jfwFormatedDate->getSubTowns();
            }

            $type = $formatedDate->getDateType();
            $dayTypes = $formatedDate->getDayTypes();

            $dayTypes = array_map(function($dayType){
                return [
                    'value'=>$dayType->getKey(),
                    'label'=>$dayType->dt_code,
                    'color'=>config('shl.color_codes')[$dayType->dt_color],
                    'dayTypes'=>[],
                    'bataType'=>null,
                    'mileage'=>0.00,
                    'areas'=>[],
                ];
            },$dayTypes);

            $description = "Not Set";
            switch ($type) {
                case 7:
                    $description = "Changed Itinerary";
                    break;
                case 5:
                    $description = "Joint Field Worker - ".$itineraryDate->joinFieldWorker->name;
                    break;
                case 4:
                    $description = "Additional Route Plan";
                    break;
                case 3:
                    $description = "Standard Itinerary";
                    break;
                case 0:
                    $description = "Not a field work day";
                    break;
                case 2:
                    $description = "Not set";
                    break;
            }

            $towns->transform(function(SubTown $subTown){
                return [
                    'value'=>$subTown->getKey(),
                    'label'=>$subTown->sub_twn_name
                ];
            });

            return [
                'date' =>$date ,
                'description' => $description,
                'dayTypes' => $dayTypes,
                'bataType'=>$bataType,
                'mileage'=>$formatedDate->getMileage(),
                'areas'=>$towns,
                'id'=>$itineraryDate->getKey()
            ];
         });
 
         $itineraryDates = $itinerary->itineraryDates;
 
         foreach($specialDays as $specialDay){
             $itineraryDate = $itineraryDates->where('date',$specialDay['date'])->first();
 
             if(!$itineraryDate){
                 $itineraryDates->push($specialDay);
             }
         }

         $itineraryDates = $itineraryDates->sortBy('date')->values();
 
         return [
             "dates"=>$itineraryDates,
             "success"=>true
        ];
    }
}