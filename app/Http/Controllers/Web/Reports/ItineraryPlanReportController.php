<?php 
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use App\Models\DayType;
use Illuminate\Support\Facades\DB;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Traits\Territory;
use App\Models\Chemist;
use App\Models\DoctorSubTown;
use Illuminate\Support\Facades\Auth;
use App\Models\SpecialDay;
use App\Models\BataCategory;
use App\Models\TeamUser;
use App\Models\User;
use App\Models\UserTeam;

class ItineraryPlanReportController extends ReportController
{

    use Territory;

    protected $title = "Itinerary Plan Report";

    protected $defaultSortColumn="u.name";

    public function search(Request $request){

        $query = DB::table('team_users AS tu')
            ->select(
                'u.name AS medical_rep_name',
                'u.id AS medical_rep_id',
                't.tm_id AS team_id',
                't.tm_name AS team_name',
                'fm.id AS field_manager_id',
                'fm.name AS field_manager_name',
                'd.divi_id AS division_id',
                'd.divi_name AS division_name',
                'hod.name AS hod_name',
                'hod.id AS hod_id'
            )
            ->join('teams AS t','t.tm_id','tu.tm_id')
            ->join('users AS u','u.id','tu.u_id')
            ->join('users AS fm','t.fm_id','fm.id')
            ->join('division AS d','d.divi_id','u.divi_id')
            ->leftJoin('users AS hod','t.hod_id','hod.id')
            ->where('u_id','!=',2)
            ->where('u_id','!=',5)
            ->whereNull('t.deleted_at')
            ->whereNull('u.deleted_at')
            ->whereNull('fm.deleted_at')
            ->whereNull('d.deleted_at')
            ->whereNull('hod.deleted_at')
            ->whereNull('tu.deleted_at')
            ->groupBy(['u.id','t.tm_id','fm.id','hod.id']);

        if($request->has('values.team.value'))
            $query->where('t.tm_id',$request->input('values.team.value'));

        if($request->has('values.medical_rep.value'))
            $query->where('u.id',$request->input('values.medical_rep.value'));

        if($request->has("values.field_manager.value"))
            $query->where('fm.id',$request->input('values.field_manager.value'));
      
        if($request->has("values.division.value"))
            $query->where('u.divi_id',$request->input('values.division.value'));

        $user = Auth::user();
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $users = UserModel::getByUser($user);
            $query->where(function($query1)use ($users){

                $query1->orWhereIn('u.id',$users->pluck('id')->all());
                $query1->orWhereIn('fm.id',$users->pluck('id')->all());
            });
        }


        $teams = UserTeam::where('u_id',$user->getKey())->get();
        if($teams->count()){
            $users = TeamUser::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
            $query->where(function($query1)use ($users){

                $query1->orWhereIn('u.id',$users->pluck('u_id')->all());
                $query1->orWhereIn('fm.id',$users->pluck('u_id')->all());
            });
        } 

        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'team':
                $sortBy='t.tm_name';
                break;
            case 'medical_rep':
                $sortBy='u.name';
                break;
            case 'field_manager':
                $sortBy='fm.name';
                break;
            case 'division':
                $sortBy='d.divi_id';
                break;
            case 'hod':
                $sortBy='hod.hod_name';
                break;
            default:
                break;
        }
        $count = $this->paginateAndCount($query,$request,'t.tm_name');

        $results = $query->get();

        $results->transform(function($data){
            $data->team = $data->team_id;
            return $data;
        });

        $dayTypes = DayType::get();
        $bataCategories = BataCategory::get();

        $month = $request->input('s_date',date('Y-m-01'));

        $s_dates = SpecialDay::whereYear('sd_date',date("Y",strtotime($month)))->whereMonth('sd_date',date("m",strtotime($month)))->select('*',DB::raw('DATE(sd_date) as formatteddate'))->get();

        $begin = new \DateTime(date('Y-m-01'));
        $end = new \DateTime(date("Y-m-t"));
        $end = $end->modify('1 day'); 

        $interval = new \DateInterval('P1D');
        $daterange = new \DatePeriod($begin, $interval ,$end);

        $sun_count = 0;
        $sat_count = 0;
        $sp_count = 0;
        // $datesCount = 0;
        foreach ($daterange as $key => $date) {

            $sp_date = $s_dates->where('formatteddate',$date->format('Y-m-d'))->first();

            if($date->format('D') == "Sat"){
                $sat_count++;
            } elseif ($date->format('D') == "Sun"){
                $sun_count++;
            } elseif ($sp_date) {
                $sp_count++;
            }
            
        }
        $dates = date('t',strtotime($month));
        $datesCount = $dates - ($sat_count+$sun_count+$sp_count);

        $newResults = [];
        $results->transform(function($row)use($dayTypes,$bataCategories,$month,$datesCount){
            $formatedRow = [];

            $dropdowns = ['team','field_manager','medical_rep','division','hod'];

            foreach($dropdowns as $dropdown){
                
                $formatedRow[$dropdown] = [
                    "value"=>$row->{$dropdown."_id"},
                    "label"=>$row->{$dropdown."_name"}
                ];
            }

            $latestItinerary = Itinerary::where('i_year','=',date("Y",strtotime($month)))
                ->where('i_month','=',date("m",strtotime($month)))
                ->where('rep_id',$row->medical_rep_id)
                // ->whereNotNull('i_aprvd_u_id')
                ->latest()
                ->first();

            $itineraryRelations = [
                'joinFieldWorker',
                'itineraryDayTypes',
                'itineraryDayTypes.dayType',
                'standardItineraryDate',
                'standardItineraryDate.bataType',
                'additionalRoutePlan',
                'additionalRoutePlan.bataType',
                'changedItineraryDate',
                'changedItineraryDate.bataType',
                'bataType',
            ];

            if($latestItinerary){
                $itineraryDates = ItineraryDate::with($itineraryRelations)->where('i_id',$latestItinerary->getKey())->get();

                // $itineraryDatesNumbers = $itineraryDates->pluck('sid_id')->filter(function($id){return !!$id;})->values();

                // $subTownIds = $this->__getSubtownsByItineraryIds($itineraryDatesNumbers->all(),[0])->pluck('sub_twn_id')->all();
                $user = User::where('id',$row->medical_rep_id)->first();

                try{
                    $itineraryTowns = $this->getTerritoriesByItinerary($user);
                } catch(\Throwable $exception) {
                    $itineraryTowns = collect();
                }
                

                $subTownIds = $itineraryTowns->pluck('sub_twn_id');
    
                $chemists = Chemist::whereIn('sub_twn_id',$subTownIds)->count();
                $doctors =  DoctorSubTown::whereIn('sub_twn_id',$subTownIds)->distinct('doc_id')->count('doc_id');

                $itineraryDates->transform(function( ItineraryDate $itineraryDate){
                    $formatedDate = $itineraryDate->getFormatedDetails();

                    return $formatedDate->jsonSerialize();
                });
            }

            $formatedRow['wdc_count'] = $datesCount;
            $formatedRow['fwd_count'] = $latestItinerary? $itineraryDates->where('fieldWorkingDay',true)->count():0;



            $latestItineraryByFM = Itinerary::where('i_year','=',date("Y",strtotime($month)))
                ->where('i_month','=',date("m",strtotime($month)))
                ->where('fm_id',$row->field_manager_id)
                ->with('itineraryDates')
                ->latest()
                ->first();
                
            $formatedRow['jfwd_count'] = $latestItineraryByFM? $latestItineraryByFM->itineraryDates->where('u_id',$row->medical_rep_id)->count():0;

            $formatedRow['kilometers'] = $latestItinerary?$itineraryDates->sum('mileage'):0;
            $formatedRow['doctor_count'] = $latestItinerary?$doctors:0;
            $formatedRow['chemist_count'] = $latestItinerary?$chemists:0;

            $dayTypeIds = collect([]);


            foreach($bataCategories as $bataCategory){
                $formatedRow[$bataCategory->getKey().'_count'] = 0;
            }


            if($latestItinerary){
                foreach($itineraryDates as $itineraryDate){
                    foreach($itineraryDate['dayTypes'] as $dayType){
                        $dayTypeIds->push(['day_type_id'=>$dayType->getKey()]);
                    }

                    $bataType = $itineraryDate['bataType'];
                    if(isset($bataType)){
                        $formatedRow[$bataType->btc_id.'_count'] ++;
                    }
                }
            }

            foreach($dayTypes as $dayType){
                $formatedDayTypeCode = strtolower(preg_replace("/[^\da-z]/i",'',$dayType->dt_code));
                $formatedRow[$formatedDayTypeCode.'_count']= $dayTypeIds->where('day_type_id',$dayType->getKey())->count();
            }

            return $formatedRow;
        });

        return [
            'count'=>$count,
            'results'=>$results
        ];
    }

    public function setColumns($columnController, Request $request){
        $columnController->ajax_dropdown('team')->setLabel("Team");
        $columnController->ajax_dropdown('hod')->setLabel("HOD");
        $columnController->ajax_dropdown('field_manager')->setLabel("Field Manager");
        $columnController->ajax_dropdown('medical_rep')->setLabel("Medical Representative");
        $columnController->number('wdc_count')->setLabel("No Of Working Days")->setSearchable();
        $columnController->number('fwd_count')->setLabel("Field Work Days")->setSearchable();
        $columnController->number('jfwd_count')->setLabel("Joint Field Work Days")->setSearchable();
        $dayTypes = DayType::get();
        foreach($dayTypes as $dayType){
            $formatedDayTypeCode = strtolower(preg_replace("/[^\da-z]/i",'',$dayType->dt_code));
            $columnController->number($formatedDayTypeCode."_count")->setLabel($dayType->dt_name." Days")->setSearchable();
        }
        $bataCategory = BataCategory::get();
        foreach ($bataCategory as  $value) {
            $columnController->number($value->btc_id."_count")->setLabel($value->btc_category)->setSearchable();
        }
        $columnController->number('kilometers')->setLabel("Planned KMs")->setSearchable();
        $columnController->number('doctor_count')->setLabel("Planned DRs")->setSearchable();
        $columnController->number('chemist_count')->setLabel("Planned Chemists")->setSearchable();
        $columnController->ajax_dropdown('division')->setLabel("Division");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team')->setValidations('');

        $inputController->ajax_dropdown('field_manager')
            ->setLabel("Field Manager")
            ->setWhere(["tm_id" => "{team}",'u_tp_id'=>config('shl.field_manager_type'),"divi_id" => "{division}"])->setLink('user')->setValidations('');

        $inputController->ajax_dropdown('medical_rep')
        ->setLabel("Medical representative/PS")
        ->setWhere(["tm_id" => "{team}",'u_tp_id'=>config('shl.medical_rep_type').'|'.config('shl.product_specialist_type'),"divi_id" => "{division}"])->setLink('user')->setValidations('');
        
        $inputController->ajax_dropdown('division')->setLabel('Division')->setLink("division")->setValidations('');
        $inputController->date('month')->setLabel('Month');
        $inputController->setStructure([
            ['team','field_manager','medical_rep'],
            ['division','month']
        ]);
    }
}