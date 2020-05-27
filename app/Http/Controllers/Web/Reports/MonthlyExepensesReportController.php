<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Form\Columns\ColumnController;
use App\Models\Reason;
use App\Models\User;
use App\Models\TeamUser;
use App\Exceptions\WebAPIException;
use App\Models\Expenses;

class MonthlyExepensesReportController extends ReportController{

     protected $title = "Monthly Expenses Report";
     public function search(Request $request){
          $values = $request->input('values',[]);

          if(!isset($values['team']))
            throw new WebAPIException("Team  field is required!");

          $query = TeamUser::with('team','user');

          if(isset($values['team']))
               $query->where('tm_id',$values['team']['value']);

          if(isset($values['user']))
               $query->where('u_id',$values['user']['value']);

          $results = $query->get();

        //   $next_year = date('Y-m-d',strtotime($final_year.'+1 year'));

          $reasons = Reason::latest()->where('rsn_type',config('shl.expenses_reason_type'))->where('rsn_id','!=',14)->get();

          $results->transform(function($row)use($reasons,$values){

               $formattedResults['emp_no'] = isset($row->user->u_code)?$row->user->u_code:null;
               $formattedResults['name'] = isset($row->user->name)?$row->user->name:null;


               foreach ($reasons as $key => $exp) {
                    $exp_amount = Expenses::where('u_id',$row->u_id)->where('rsn_id',$exp->rsn_id)->whereDate('exp_date','>=',date('Y-m-01',strtotime($values['month'])))->whereDate('exp_date','<=',date('Y-m-d',strtotime($values['month'])))->sum('exp_amt');
                    $ytd_exp_amount = Expenses::where('u_id',$row->u_id)->where('rsn_id',$exp->rsn_id)->whereDate('exp_date','>=',date('Y-m-01'))->whereDate('exp_date','<=',date('Y-m-t'))->sum('exp_amt');

                    $formattedResults['exp_'.$exp->rsn_id] = $exp_amount;
                    $formattedResults['ytd_exp_'.$exp->rsn_id] = $ytd_exp_amount;
               }
               return $formattedResults;
          });

          $totalRow = [];
          foreach ($reasons as $key => $row) {
               $totalRow['exp_'.$row->rsn_id] = round($results->sum('exp_'.$row->rsn_id),2);
               $totalRow['ytd_exp_'.$row->rsn_id] = round($results->sum('ytd_exp_'.$row->rsn_id),2);
          }

          $totalRow['name'] = 'Total';
          $totalRow['special'] = true;
          $results->push($totalRow);

          return [
               'count' => 0,
               'results' => $results
          ];
     }

     protected function getAdditionalHeaders($request){

          $first_row = [
               "title"=>"",
               "colSpan"=>2
          ];

          $second_row = [
               "title"=>"Month",
               "colSpan"=>4
          ];

          $third_row = [
               "title"=>"YTD Total",
               "colSpan"=>4
          ];

          $columns = [[
              $first_row,
              $second_row,
              $third_row
          ]];
          return $columns;
     }

     public function setColumns(ColumnController $columnController, Request $request){

          $reasons = Reason::latest()->where('rsn_type',config('shl.expenses_reason_type'))->where('rsn_id','!=',14)->get();

          $columnController->text("emp_no")->setLabel("Emp No");
          $columnController->text("name")->setLabel("Name");

          foreach ($reasons as $key => $exp) {
               $columnController->number("exp_".$exp->rsn_id)->setLabel($exp->rsn_name);
          }

          foreach ($reasons as $key => $exp) {
               $columnController->number("ytd_exp_".$exp->rsn_id)->setLabel($exp->rsn_name);
          }
     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown("area_id")->setLabel("Area")->setLink("area")->setValidations('');
          $inputController->ajax_dropdown("divi_id")->setLabel("Division")->setLink("division")->setValidations('');
          $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team');
          $inputController->ajax_dropdown("user")->setWhere(['tm_id'=>"{team}"])->setLabel("PS/MR and FM")->setLink("user");
          $inputController->text("year")->setLabel("Year");
          $inputController->date("month")->setLabel("month");

          $inputController->setStructure([["team","user","divi_id"],["area_id","month"]]);
     }
}
?>
