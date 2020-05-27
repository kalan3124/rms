<?php
namespace App\Http\Controllers\Web\Reports;

use App\Models\Expenses;
use App\Form\Columns\ColumnController;
use Illuminate\Http\Request;
use App\Models\TeamUser;

class ExpensesBillReportController extends ReportController {
    protected $title = "Expenses Bill Report";

    public function search(Request $request){

        $fromDate = $request->input('values.from_date',date('Y-m-d'));
        $toDate = $request->input('values.to_date',date('Y-m-d'));
        $teamId = $request->input('values.team.value');
        $userId = $request->input('values.user.value');
        $reasonId = $request->input('values.reason.value');

        $query = Expenses::query();
        $query->orderBy('u_id', 'DESC');

        $query->whereDate('exp_date','>=',$fromDate);
        $query->whereDate('exp_date','<=',$toDate);

        if(isset($teamId)&&!isset($userId)){
            $teamUsers = TeamUser::where('tm_id',$teamId)->get();
            $query->whereIn('u_id',$teamUsers->pluck('u_id'));
        }

        if(isset($userId)){
            $query->where('u_id',$userId);
        }

        if(isset($reasonId)){
            $query->where('rsn_id',$reasonId);
        }

        $grandTotal = $query->sum('exp_amt');

        $count = $this->paginateAndCount($query,$request,'u_id');

        $query->with(['reason','user','reason','user.teamUser','user.teamUser.team']);

        $results = $query->get();

        $newResults = [];
        $results->transform(function($row){
            return [
                'team'=>$row->user&&$row->user->teamUser&&$row->user->teamUser->team?[
                    'label'=>$row->user->teamUser->team->tm_name,
                    'value'=>$row->user->teamUser->team->tm_id
                ]:null,
                'user'=>$row->user?[
                    'label'=>$row->user->name,
                    'value'=>$row->user->id
                ]:null,
                'reason'=>$row->reason?[
                    'label'=>$row->reason->rsn_name,
                    'value'=>$row->reason->rsn_id
                ]:null,
                'amount'=>$row->exp_amt,
                'remark'=>$row->exp_remark,
                'image'=>$row->image_url,
                'app_version'=>$row->app_version,
                'exp_date'=>$row->exp_date,
                'created_date'=>$row->created_at->format('Y-m-d H:i:s'),
            ];
        });
        $tm_id =0;
        $tot_team_amount = 0;
        $tot_user_amount = 0;
        foreach ($results as $key => $value) {
            $tot_team_amount += $value['amount'];
            $tot_user_amount += $value['amount'];
            $newResults[] = $value;
            if($key != count($results)-1){
                $per_tm_id = $results[$key+1]['team']['value'];
                $new_tm_id = $value['team']['value'];

                $per_u_id = $results[$key+1]['user']['value'];
                $new_u_id = $value['user']['value'];

                if($per_u_id != $new_u_id){
                    $newResults[] = [
                        'user'=>[
                            'label'=>'Total Of '.$value['user']['label'],
                            'value'=>'Total'
                        ],
                        'amount'=>number_format($tot_user_amount,2),
                        'special'=> true,
                    ];
                    $tot_user_amount = 0;

                }

                if($per_tm_id != $new_tm_id){
                    $newResults[] = [
                        'team'=>[
                            'label'=>'Total Of '.$value['team']['label'],
                            'value'=>'Total'
                        ],
                        'amount'=>number_format($tot_team_amount,2),
                        'special'=> true,
                    ];
                    $tot_team_amount = 0;

                }
            }

        }

        // foreach ($results as $key => $value) {
        //     $tot_amount += $value['amount'];
        //     $newResults[] = $value;
        //     if($key != count($results)-1){
        //         $per_u_id = $results[$key+1]['user']['value'];
        //         $new_u_id = $value['user']['value'];
                // if($per_u_id != $new_u_id){
                //     $newResults[] = [
                //         'user'=>[
                //             'label'=>'User Total',
                //             'value'=>'Total'
                //         ],
                //         'amount'=>number_format($tot_amount,2),
                //         'special'=> true,
                //     ];
                //     $tot_amount = 0;

                // }
        //     }

        // }

        $amount = $results->sum('amount');

        // $results = $results->toArray();

        $newResults[] = [
            'team'=>[
                'label'=>'Page Total',
                'value'=>0
            ],
            'amount'=> number_format($amount,2,'.',''),
            'special' => true
        ];

        $newResults[] = [
            'team'=>[
                'label'=>'Grand Total',
                'value'=>0
            ],
            'amount'=>number_format($grandTotal,2),
            'special' => true
        ];


        return[
            'count'=> $count,
            'results'=> $newResults
        ];
    }

    protected function setColumns(ColumnController $columnController,Request $request){

         $columnController->ajax_dropdown('team')->setLabel("Team");
         $columnController->ajax_dropdown('user')->setLabel("User");

         $columnController->ajax_dropdown('reason')->setLabel("Reason");
         $columnController->number('amount')->setLabel("Amount");
         $columnController->text('remark')->setLabel("Remark");
         $columnController->image('image')->setLabel("Image");
         $columnController->text('app_version')->setLabel("App Version");
         $columnController->text('exp_date')->setLabel("Expense date");
         $columnController->text('created_date')->setLabel("Created date");
    }
    protected function setInputs($inputController){
        $inputController->ajax_dropdown('reason')->setLabel("Reason")->setLink('reason')->setValidations('');
        $inputController->ajax_dropdown("team")->setLabel("Team")->setLink("team")->setValidations('');
        $inputController->ajax_dropdown("division")->setLabel("Division")->setLink("division")->setValidations('');
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['tm_id'=>'{team}','divi_id'=>'{division}'])->setValidations('');
        $inputController->date('from_date')->setLabel('From')->setValidations('');
        $inputController->date('to_date')->setLabel('To')->setValidations('');

        $inputController->setStructure([
            ["reason",'team','user'],
            ['division','from_date','to_date']
        ]);
    }
}
