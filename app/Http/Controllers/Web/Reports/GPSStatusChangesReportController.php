<?php
namespace App\Http\Controllers\Web\Reports;

use App\Models\GPSStatusChange;
use App\Models\Team;
use App\Models\TeamUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GPSStatusChangesReportController extends ReportController {
    protected $title = "GPS Status Changes Report";

    protected $defaultSortColumn="e_time";

    public function search(Request $request){

        $division = $request->input('values.division.value');
        $team = $request->input('values.team.value');
        $user = $request->input('values.user.value');
        $startDate = $request->input('values.s_date');
        $endDate = $request->input('values.e_date');

        $sortBy = $request->input('sortBy');

        if(!$sortBy)
            $sortBy='e_time';

        $sortColumnMapping = [
            'user'=>'u.name',
            'e_time'=>'gsc.gsc_time',
            'e_btry'=>'gsc.gsc_btry',
            'e_accuracy'=>'gsc.gsc_accu',
        ];

        $query = DB::table('gps_status_change AS gsc')
            ->join('users AS u','u.id','gsc.u_id')
            ->leftJoin('division AS d','d.divi_id','u.divi_id')
            ->where('gsc.gsc_status',1)
            ->groupBy('gsc.u_id','gsc.gsc_time','gsc.gsc_status');

        if($division){
            $query->where('u.divi_id',$division);
        }

        if($team){
            $team = Team::with('teamUsers')->find($team);

            if($team){

                $teamUsers = $team->teamUsers;
                $teamUsers = $teamUsers->pluck('u_id');
                $teamUsers->push($team->fm_id);
    
                $query->whereIn('u.id',$teamUsers->all());
            }
        }

        if($user){
            $query->where('u.id',$user);
        }
        
        if($startDate&&$endDate){
            $query->whereDate('gsc.gsc_time','>=',$startDate);
            $query->whereDate('gsc.gsc_time','<=',$endDate);
        }

        $query->select(['u.u_tp_id','d.divi_id','d.divi_name','u.name','gsc.u_id','gsc.gsc_time','gsc.gsc_btry','gsc.gsc_accu','gsc.gsc_lat','gsc.gsc_lon']);

        $count = $this->paginateAndCount($query,$request,$sortColumnMapping[$sortBy]);

        $results = $query->get();

        $results->transform(function($result){
            $nextCoordinate = GPSStatusChange::where('u_id',$result->u_id)->where('gsc_status',0)->where('gsc_time','>=',$result->gsc_time)->orderBy('gsc_time','asc')->first();

            $team = null;

            if(config('user.field_manager_type')==$result->u_tp_id)
                $team = Team::where('fm_id',$result->u_id)->latest()->first();
            else {
                $teamUser = TeamUser::with('team')->where('u_id',$result->u_id)->latest()->first();

                if($teamUser){
                    $team = $teamUser->team;
                }
            }

            return [
                'division'=>[
                    'value'=>$result->divi_id,
                    'label'=>$result->divi_name
                ],
                'team'=>$team?[
                    'value'=>$team->tm_id,
                    'label'=>$team->tm_name
                ]:[
                    'value'=>0,
                    'label'=>"DELETED"
                ],
                'user'=>[
                    'value'=>$result->u_id,
                    'label'=>$result->name
                ],
                'e_time'=>$result->gsc_time,
                'e_btry'=>$result->gsc_btry,
                'e_accuracy'=>$result->gsc_accu,
                'e_position'=>[
                    'label'=>$result->gsc_lat.','.$result->gsc_lon,
                    'link'=>'https://www.google.com/maps/search/?api=1&query='.$result->gsc_lat.','.$result->gsc_lon
                ],
                'a_time'=>($nextCoordinate)?$nextCoordinate->gsc_time:null,
                'a_btry'=> $nextCoordinate? $nextCoordinate->gsc_btry:null,
                'a_accuracy'=> $nextCoordinate? $nextCoordinate->gsc_accu:null,
            ];

        });
        
        return [
            'count'=>$count,
            'results'=>$results
        ];

    }

    protected function getAdditionalHeaders($request){

        $columns = [[
            [
                "title"=>"",
                "colSpan"=>3
            ],
            [
                "title"=>"Deactivated",
                "colSpan"=>4
            ],
            [
                "title"=>"Activated",
                "colSpan"=>3
            ],
        ]];

        return $columns;
    }


    protected function setColumns($columnController, Request $request){
        $columnController->ajax_dropdown('division')->setLabel("Division")->setSearchable(false);
        $columnController->ajax_dropdown('team')->setLabel("Team")->setSearchable(false);
        $columnController->ajax_dropdown('user')->setLabel("User");
        $columnController->date('e_time')->setLabel("Time");
        $columnController->date('e_btry')->setLabel("Battery");
        $columnController->date('e_accuracy')->setLabel("Accuracy");
        $columnController->link('e_position')->setLabel("Position")->setSearchable(false);
        $columnController->date('a_time')->setLabel("Time")->setSearchable(false);
        $columnController->date('a_btry')->setLabel("Battery")->setSearchable(false);
        $columnController->date('a_accuracy')->setLabel("Accuracy")->setSearchable(false);
    }

    protected function setInputs($inputController){
        $inputController->ajax_dropdown('division')->setLabel("Division")->setLink('division');
        $inputController->ajax_dropdown('team')->setLabel("Team")->setLink('team')->setWhere(['divi_id'=>"{division}"]);
        $inputController->ajax_dropdown('user')->setLabel("User")->setLink('user')->setWhere(['divi_id'=>"{division}",'tm_id'=>"{team}"]);
        $inputController->date('s_date')->setLabel("Start Date");
        $inputController->date('e_date')->setLabel("End Date");
        $inputController->setStructure([['division','team','user'],['s_date','e_date']]);
    }
}