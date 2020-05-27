<?php
namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Traits\Territory;
use App\Models\UserCustomer;
use App\Models\Chemist;
use App\Models\ProductiveVisit;
use App\Models\DoctorSubTown;
use App\Models\Doctor;
use App\Models\User;

class CoverageSummaryReportController extends ReportController
{
    use Territory;

    protected $title = "Coverage Summary Report";

    protected $defaultSortColumn="tm_name";

    public function search(Request $request){
        $teamQuery = DB::table('team_users AS tu')
        ->join('teams AS t','tu.tm_id','=','t.tm_id')
        ->join('users AS u','u.id','=','tu.u_id')
        ->select('t.tm_name','u.id AS medical_rep_id','u.name','t.fm_id AS field_manager_id','u.divi_id')
        ->whereNull('t.deleted_at')->whereNull('tu.deleted_at')->whereNull('u.deleted_at')
        ->groupBy(['u.id','t.tm_id']);

        if($request->has('values.team_id.value')){
            $teamQuery->where('t.tm_id',$request->input('values.team_id.value'));
        }
        if($request->has('values.divi_id.value')){
            $teamQuery->where('u.divi_id',$request->input('values.divi_id.value'));
        }

        if($request->has('values.user.value')){
            $teamQuery->where('u.id',$request->input('values.user.value'));
        }

        $sortMode = $request->input('sortMode')??'desc';
        $sortBy = 't.tm_name';

        switch ($request->input('sortBy')) {
            case 'mr_name':
                $sortBy='u.name';
                break;
            default:
                break;
        }

        $teamQuery->orderBy($sortBy,$sortMode);

        $count = $this->paginateAndCount($teamQuery,$request);
        $teamResults = $teamQuery->get();

        $month = $request->input('values.month');

        $teamResults->transform(function($row)use($month){

            $chemist_planned_calls = 0;
            $met_chemists = 0;
            $missed_chemist = 0;
            $chem_percentage = 0;

            $doc_planned_calls = 0;
            $met_doctors = 0;
            $missed_doctor = 0;
            $doc_percentage = 0;

            $latestItineraryByRep = Itinerary::where('rep_id',$row->medical_rep_id)
                ->whereNotNull('i_aprvd_at')
                ->where('i_year','=',date("Y",strtotime($month)))
                ->where('i_month','=',date("m",strtotime($month)))
                ->latest()
                ->first();

                if($latestItineraryByRep){

                $itineraryDates = ItineraryDate::with('itineraryDayTypes')->where('i_id',$latestItineraryByRep->getKey())->get();

                $subTownIds = $this->__getSubtownsByItineraryIds($itineraryDates->pluck('sid_id')->all(),[0])->pluck('sub_twn_id')->all();

                $user = User::find($row->medical_rep_id);
                // Getting assigned customers for user
                $assignedCustomers = UserCustomer::getByUser($user);

                $doctorIds = $assignedCustomers->pluck('doc_id');
                $chemistIds = $assignedCustomers->pluck('chemist_id');

                // Getting chemists for above time ids
                $chemists = Chemist::whereIn('sub_twn_id',$subTownIds)->whereIn('chemist_id',$chemistIds)->get();

                $from = Carbon::parse(date("Y-m",strtotime($month))."-01")
                 ->startOfDay()        // 2018-09-29 00:00:00.000000
                 ->toDateTimeString(); // 2018-09-29 00:00:00

                $to = Carbon::parse(date("Y-m",strtotime($month))."-31")
                 ->endOfDay()          // 2018-09-29 23:59:59.000000
                 ->toDateTimeString(); // 2018-09-29 23:59:59

                $chemProductiveVisit = ProductiveVisit::whereIn('chemist_id',$chemists->pluck('chemist_id')->all())
                ->whereBetween('pro_start_time', [$from, $to])
                ->get();

                //getting missed chemist
                $missedChemist = $chemists->whereNotIn('chemist_id',$chemProductiveVisit->pluck('chemist_id')->all());

                // Getting doctors for today
                $doctors = DB::table('doctor_intitution AS ti')
                ->join('institutions AS i','i.ins_id','=','ti.ins_id','inner')
                ->whereIn('i.sub_twn_id',$subTownIds)
                ->where([
                    'i.deleted_at'=>null,
                    'ti.deleted_at'=>null
                ])
                ->whereIn('doc_id',$doctorIds)
                ->select('ti.doc_id')
                ->groupBy('ti.doc_id')
                ->get();
                //getting doctors from subtown assignment
                $doctorsBySubTown = DoctorSubTown::whereIn('sub_twn_id',$subTownIds)->whereIn('doc_id',$doctorIds)->get();
                //merge subtown doctors with institute assigned doctors
                $doctors = $doctors->merge($doctorsBySubTown);

                $docProductiveVisit = ProductiveVisit::whereIn('doc_id',$doctors->pluck('doc_id')->all())
                ->whereBetween('pro_start_time', [$from, $to])
                ->get();

                $missedDoctorIds = $doctors->whereNotIn('doc_id',$docProductiveVisit->pluck('doc_id')->all());
                $missedDoctors = Doctor::whereIn('doc_id',$missedDoctorIds->pluck('doc_id')->all())->get();

                $chemist_planned_calls = $chemists->count('chemist_id');
                $met_chemists = $chemProductiveVisit->count('chemist_id');
                $missed_chemist = $missedChemist->count('chemist_id');

                if($chemist_planned_calls == 0){
                    $chemist_planned_calls = 1;
                }

                $chem_percentage = $met_chemists/$chemist_planned_calls;


                $doc_planned_calls = $doctors->count('doc_id');
                $met_doctors = $docProductiveVisit->count('doc_id');
                $missed_doctor = $missedDoctors->count('doc_id');

                if($doc_planned_calls == 0){
                    $doc_planned_calls = 1;
                }
                $doc_percentage = $met_doctors / $doc_planned_calls;

                }

            return [
                'tm_name'=>$row->tm_name.' / '.$row->name,
                // 'mr_name'=>$row->name,

                'doc_planned_calls'=>$doc_planned_calls,
                'met_doctors'=>$met_doctors,
                'missed_doctor'=>$missed_doctor,
                'dr_percentage'=>number_format($doc_percentage,2),

                'chem_plan_calls'=>$chemist_planned_calls,
                'chem_mets'=>$met_chemists,
                'missed_chemist'=>$missed_chemist,
                'chem_percentage'=> number_format($chem_percentage,2)
            ];
        });

        $row = [
            'special' => true,
            'doc_planned_calls' =>number_format($teamResults->sum('doc_planned_calls')),
            'met_doctors' =>number_format($teamResults->sum('met_doctors')),
            'missed_doctor' =>number_format($teamResults->sum('met_doctors')),
            'chem_plan_calls' =>number_format($teamResults->sum('chem_plan_calls')),
            'chem_mets' =>number_format($teamResults->sum('chem_mets')),
            'missed_chemist' =>number_format($teamResults->sum('missed_chemist'))
       ];

       $teamResults->push($row);

        return [
            'count'=>$count,
            'results'=>$teamResults
        ];
    }
    protected function getAdditionalHeaders($request){
        $columns = [[
            [
                "title"=>"",
                "colSpan"=>1
            ],
            [
                "title"=>"Dr Coverage",
                "colSpan"=>4
            ],
            [
                "title"=>"Chemist Coverage",
                "colSpan"=>5
            ]
        ]];
        return $columns;
    }

    public function setColumns($columnController, Request $request){
        $columnController->text('tm_name')->setLabel("Name");
        // $columnController->text('mr_name')->setLabel("MR Name");

        $columnController->text('doc_planned_calls')->setLabel('Planned Doc Call');
        $columnController->text('met_doctors')->setLabel('Met Call');
        $columnController->text('missed_doctor')->setLabel('Missed Doctors');
        $columnController->text('dr_percentage')->setLabel('%');

        $columnController->text('chem_plan_calls')->setLabel("Planned Call");
        $columnController->text('chem_mets')->setLabel("Met Call");
        $columnController->text('missed_chemist')->setLabel('Missed Chemist');
        $columnController->text('chem_percentage')->setLabel('%');
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('divi_id')->setLabel('Division')->setLink('division')->setValidations('');
        $inputController->ajax_dropdown('team_id')->setLabel('Team')->setLink('team')->setValidations('');
        $inputController->ajax_dropdown('user')->setWhere(["tm_id" => "{team_id}","divi_id" => "{divi_id}",'u_tp_id'=>'3'.'|'.config('shl.product_specialist_type')])->setLabel('PS/MR')->setLink('user')->setValidations('');
        $inputController->date("month")->setLabel('Month')->setValidations('');

        $inputController->setStructure([
            'team_id',
            ['divi_id','month','user']
        ]);
    }

}
