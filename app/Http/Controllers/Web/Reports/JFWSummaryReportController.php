<?php

namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use App\Traits\Territory;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Team;
use App\Models\Itinerary;
use Validator;
use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;

class JFWSummaryReportController extends ReportController
{
    use Territory;
    protected $title = "JFW Summary Report";

    // protected $defaultSortColumn="chemist_code";

    public function search(Request $request){

        $validation = Validator::make($request->all(),[
            'values'=>'required|array',
            'values.month'=>'required|date'
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $month = $request->input('values.month',date('Y-m-d'));
        $year = date('Y',strtotime($month));
        $month = date('m',strtotime($month));

        $fieldManagersQuery = User::where('u_tp_id',config('shl.field_manager_type'));

        if($request->input('values.tm_id')){
            $teamValue = $request->input('values.tm_id');

            $team = Team::find($teamValue['value']);

            if($team){
                $fieldManagersQuery->where('id',$team->fm_id);
            }
        }

        if($request->input('values.divi_id')){
            $fieldManagersQuery->where('divi_id',$request->input('values.divi_id.value'));
        }

        if($request->input('values.u_id')){
            $fieldManagersQuery->where('id',$request->input('values.u_id.value'));
        }

        $fieldManagers = $fieldManagersQuery->get();

        $itineraries = collect([]);

        foreach($fieldManagers as $fieldManager){
            $itinerary = Itinerary::where([
                'i_year' => $year,
                'i_month' => $month,
            ])
            ->where('fm_id',$fieldManager->getKey())
            ->whereNotNull('i_aprvd_at')
            ->latest()
            ->first();

            if($itinerary)
                $itineraries->push($itinerary);
        }

        $itineraryDates = DB::table('itinerary AS i')
            ->select(['*',DB::raw('COUNT(*) AS count_days'),DB::raw('GROUP_CONCAT(id_date) AS days')])
            ->join('itinerary_date AS id','i.i_id','id.i_id')
            ->join('users AS u','u.id','i.fm_id')
            ->groupBy(['id.u_id','i.fm_id'])
            ->whereNotNull('id.u_id')
            ->where('i.i_year',$year)
            ->where('i.i_month',$month)
            ->whereIn('i.i_id',$itineraries->pluck('i_id')->all())
            ->get();

        $grouped = $itineraryDates->groupBy('fm_id');

        $results = [];

        foreach($grouped as $fm_id =>$rows){
            $teamCount = 0;
            foreach($rows as $key =>$mr){

                $user = User::find($mr->u_id);
                $team = Team::where('fm_id',$mr->fm_id)->latest()->first();

                $subTowns = collect([]);
                $days = array_unique(explode(',',$mr->days));
                if(!empty($days)&&$days[0]!=""){

                     foreach ($days as $day) {
                         try{
                              $subTownsToday = $this->getTerritoriesByItinerary($user,strtotime($year."-".str_pad($month,2,'0',STR_PAD_LEFT)."-".str_pad($day,2,'0',STR_PAD_LEFT)));
                          } catch(\Exception $e){
                              $subTownsToday = collect([]);
                          }
                          $subTowns = $subTowns->concat($subTownsToday);
                     }

                }


                $subTowns->transform(function($subTown){
                    return $subTown->sub_twn_name;
                });

                $subTownNames = implode('/ ',$subTowns->all());

                $hod_name = User::where('id',$team['hod_id'])->first();
                if(isset($team)){
                    $results[] = [
                        's_manager' => $hod_name->name,
                        'pm_fm'=>$mr->name,
                        'tm_name'=>$team['tm_name'],
                        'name_of_mr' => $user['name'],
                        't_name' => $subTownNames,
                        'dates_planned' => implode(',',$days),
                        //    'sub_town_count'=>$subTowns->count(),
                        'no_of_dates'=>COUNT($days),
                    ];

                    $teamCount +=COUNT($days);
                }
            }

            if(isset($team)){
                $results[] = [
                    's_manager' => 'Summery of',
                    'pm_fm'=>"",
                    'tm_name'=>$team['tm_name'],
                    'name_of_mr' => "",
                    't_name' => "",
                    'dates_planned' => "",
                    //    'sub_town_count'=>$subTowns->count(),
                    'no_of_dates'=>"",
                    'grand_total'=>$teamCount,
                    "special"=>true
                ];
            }

        }



        $grand_tot = array_sum(array_column($results,'grand_total'));

        $total_dates = [
            's_manager' => 'Total',
            'pm_fm'=>'',
            'tm_name'=>'',
            'name_of_mr' => '',
            't_name' => '',
            'dates_planned' => '',
            // 'sub_town_count'=>'',
            'no_of_dates'=>'',
            'team_total'=>"",
            'grand_total'=>number_format($grand_tot,2),
            'special'=>true
        ];

        array_push($results,$total_dates);



        return[
            'count'=>0,
            'results'=>$results
        ];
    }

    protected function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('s_manager')->setLabel("Senior Manager ");
        $columnController->text('pm_fm')->setLabel("PM/FM Name");
        $columnController->text('tm_name')->setLabel("Teams");
        $columnController->text('name_of_mr')->setLabel("Name of MR");
        $columnController->text('t_name')->setLabel("Town");
        $columnController->text('dates_planned')->setLabel("Dates Planned");
        $columnController->text('no_of_dates')->setLabel("No of Days");
        $columnController->number('team_total')->setLabel("Team Total");
        $columnController->number('grand_total')->setLabel("Grand Total");
        // $columnController->ajax_dropdown('twn')->setLabel("Town Name");
    }

    protected function setInputs($inputController){
        $inputController->ajax_dropdown("divi_id")->setLabel("Division")->setLink("Division")->setValidations('');
        $inputController->ajax_dropdown("tm_id")->setLabel("Team")->setLink("team")->setValidations('');
        $inputController->ajax_dropdown("u_id")->setLabel("Field Manager")->setLink("user")->setWhere([
            'u_tp_id'=>config('shl.field_manager_type'),
            'divi_id'=>'{divi_id}',
            'tm_id'=>'{tm_id}'
        ])->setValidations('');
        // $inputController->ajax_dropdown("pro_id")->setLabel("Product")->setLink("product");
        // $inputController->ajax_dropdown("ar_id")->setLabel("Area")->setLink("area");
        $inputController->date("month")->setLabel("month");
        // $inputController->ajax_dropdown("chemist_id")->setLabel("Chemist")->setLink("chemist");
        $inputController->setStructure([
            ['tm_id','divi_id','u_id'],
            ["month"]
            ]);
    }
}
