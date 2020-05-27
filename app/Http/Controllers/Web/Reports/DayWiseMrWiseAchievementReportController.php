<?php
namespace App\Http\Controllers\Web\Reports;

use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;
use App\Form\Inputs\InputController;
use App\Models\MonthWiseAchievement;
use App\Models\UserTarget;
use DateInterval;
use DatePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DayWiseMrWiseAchievementReportController extends ReportController {
    protected $title = "Day Wise MR Wise Achievement Report";

    protected $defaultSortColumn="team";

    protected $updateColumnsOnSearch = true;

    public function search(Request $request){

        $validation = Validator::make($request->all(),[
            'values.s_date'=>'required|date',
            'values.e_date'=>'required|date'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Start date and End Date is required!");
        }

        $diviId = $request->input('values.division.value');
        $teamId = $request->input('values.team.value');
        $userId = $request->input('values.user.value');

        $startDate = $request->input('values.s_date');
        $endDate = $request->input('values.e_date');

        $query = DB::table('teams As t')
            ->join('team_users AS tu', 'tu.tm_id','t.tm_id')
            ->join('users AS u','u.id','tu.u_id')
            ->whereNull('t.deleted_at')
            ->whereNull('tu.deleted_at')
            ->whereNull('u.deleted_at')
            ->select([
                't.tm_id',
                't.tm_name',
                'u.u_code',
                'u.id',
                'u.name'
            ]);

        $sortBy = 't.tm_id';

        switch ($sortBy) {
            case 'team':
                $sortBy = 't.tm_id';
                break;
            case 'user_code':
                $sortBy = 'u.u_code';
                break;
            case 'user':
                $sortBy = 'u.name';
                break;
        }

        if($diviId){
            $query->where('t.divi_id',$diviId);
        }

        if($teamId){
            $query->where('t.tm_id',$teamId);
        }

        if($userId){
            $query->where('u.id',$userId);
        }

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $results = $query->get();

        $achievements = DB::table('month_wise_achievement AS mwa')
            ->select(
                DB::raw("CONCAT(mwa_year,'-',LPAD(mwa_month,2,'0'),'-',LPAD(mwa_day,2,'0')) AS date"),
                'u_id',
                DB::raw('SUM(mwa_amount) AS amount')
            )
            ->where(DB::raw("CONCAT(mwa_year,'-',mwa_month,'-',mwa_day)"),'>=',$startDate)
            ->where(DB::raw("CONCAT(mwa_year,'-',mwa_month,'-',mwa_day)"),'>=',$endDate)
            ->whereIn('u_id',$results->pluck('id')->all())
            ->groupBy('mwa_year','mwa_month','mwa_day','u_id')
            ->get();

        $results->transform(function($row) use ($achievements, $startDate, $endDate) {
            $startDate = date_create($startDate);
            $endDate = date_create($endDate.' + 1 day');

            $targetAmount = 0;

            $interval = DateInterval::createFromDateString('1 month');
            $periodMonthWise = new DatePeriod($startDate, $interval, $endDate);

            foreach ($periodMonthWise as $dt) {
                /** @var UserTarget $target */
                $target = UserTarget::where('ut_year',$dt->format('Y'))
                    ->with('userProductTargets')
                    ->where('ut_month',$dt->format('m'))
                    ->where('u_id',$row->id)
                    ->latest()
                    ->first();

                if($target){
                    $targetAmount += $target->userProductTargets->sum('upt_value');
                }
            }


            $formated =  [
                'team'=>[
                    'value'=>$row->tm_id,
                    'label'=>$row->tm_name
                ],
                'user_code'=>$row->u_code,
                'user'=>[
                    'value'=>$row->id,
                    'label'=>$row->name
                ],
                'target'=>$targetAmount
            ];

            $interval = DateInterval::createFromDateString('1 day');
            $periodDayWise = new DatePeriod($startDate, $interval, $endDate);

            foreach ($periodDayWise as $dt) {
                $achievement = $achievements
                    ->where('date',$dt->format('Y-m-d'))
                    ->where('u_id',$row->id)
                    ->first();

                if($achievement){
                    $formated[$dt->format('Y_m_d')] = $achievement->amount;
                } else {
                    $formated[$dt->format('Y_m_d')] = 0.00;
                }
            }

            return $formated;
        });
        
        return [
            'count'=>$count,
            'results'=>$results
        ];
    }

    protected function getAdditionalHeaders($request){

        $dateDif = 0 ;

        if($request&&$request->input('values.s_date')&&$request->input('values.e_date')){
            $startDate = date_create($request->input('values.s_date'));
            $endDate = date_create($request->input('values.e_date').' + 1 day');

            $dateDif = date_diff($startDate,$endDate)->days;
        }

        $columns = [[
            [
                "title"=>"",
                "colSpan"=>4
            ],
            [
                "title"=>"Day Wise Sales Achievement",
                "colSpan"=>$dateDif
            ]
        ]];

        return $columns;
    }

    public function setColumns( ColumnController $columnController, Request $request){
        $columnController->ajax_dropdown('team')->setLabel('Team');
        $columnController->text('user_code')->setLabel('MR Code');
        $columnController->ajax_dropdown('user')->setLabel("User");
        $columnController->number('target')->setLabel("Target")->setSearchable(false);

        if($request&&$request->input('values.s_date')&&$request->input('values.e_date')){
            $startDate = date_create($request->input('values.s_date'));
            $endDate = date_create($request->input('values.e_date').' + 1 day');

            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($startDate, $interval, $endDate);

            foreach ($period as $dt) {
                $columnController->number($dt->format("Y_m_d"))->setLabel($dt->format('Y-m-d'))->setSearchable(false);
            }
        }
    }

    public function setInputs( InputController $inputController){
        $inputController->ajax_dropdown('division')->setLabel('Division')->setLink('division');
        $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team')->setWhere(['divi_id'=>'{division}']);
        $inputController->ajax_dropdown('user')->setLabel('MR Name')->setLink('user')->setWhere(['tm_id'=>'{team}']);
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');

        $inputController->setStructure([
            ['division','team','user'],
            ['s_date','e_date']
        ]);
    }
}