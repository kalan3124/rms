<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Form\Columns\ColumnController;
use App\Exceptions\WebAPIException;
use App\Models\DoctorSubTown;
use App\Models\SubTown;
use App\Models\Promotion;
use App\Models\DoctorInstitution;

class PromotionalPlanReportController extends ReportController{

     protected $title = "Promotional Plan Report";

     public function search(Request $request){
          $values = $request->input('values');

          $year_month = date('m',strtotime($values['month']));
          // return $year_month;
          $query = DB::table('users as u')
               ->select('u.id','u.name','dpbmr.promo_value','dpbmr.promo_date','d.doc_name','tu.tm_id','d.doc_id','dpbmr.promo_id')
               ->join('doctor_promotion_by_mr as dpbmr','dpbmr.u_id','u.id')
               ->join('doctors as d','d.doc_id','dpbmr.doc_id')
               ->join('team_users as tu','u.id','tu.u_id')
               ->where('u.u_tp_id','3'.'|'.config('shl.product_specialist_type'))
               ->whereDate('dpbmr.promo_date','LIKE','%'.$year_month.'%')
               ->whereNull('u.deleted_at')
               ->whereNull('dpbmr.deleted_at')
               ->whereNull('d.deleted_at')
               ->whereNull('tu.deleted_at');

               if(!$request->has('values.team_id.value'))
                    throw new WebAPIException("Team field is required");

               if($request->has('values.team_id.value')){
                    $query->where('tu.tm_id',$request->input('values.team_id.value'));
               }


               $results = $query->get();

          $formattedResults = [];

          foreach ($results as $key => $row) {
               $doc_sub_towns = DoctorSubTown::with('subTown')->where('doc_id',$row->id)->get();
               $doc_towns = $doc_sub_towns->transform(function($doc_sub_town){
                    if(isset($doc_sub_town->subTown))
                         $return['sub_name'] = $doc_sub_town->subTown->sub_twn_name;
                    else
                         $return['sub_name'] = "-";
                    return $return;
               });

               $promo = Promotion::where('promo_id',$row->promo_id)->first();

               $doc_insts = DoctorInstitution::with('institution')->where('doc_id',$row->id)->get();
               $doc_insts->transform(function($doc_inst){
                    if(isset($doc_inst->institution))
                         $return['doc_inst'] = $doc_inst->institution->ins_short_name;
                    else
                         $return['doc_inst'] = "-";
                    return $return;
               });

               $town_name = implode(', ',$doc_towns->pluck('sub_name')->all());
               $inst_name = implode(', ',$doc_insts->pluck('doc_inst')->all());

               $formattedResults [] = [
                    'ps_name' => $row->name,
                    'date' => date('Y-m-d',strtotime($row->promo_date)),
                    'prn_number' => '-',
                    'amount' => isset($row->promo_value)?number_format($row->promo_value,2):'0.00',
                    'amount_new' => isset($row->promo_value)?$row->promo_value:'0.00',
                    'ins_town' => $inst_name." / ".$town_name,
                    'act_dr_name' => $promo->promo_name." / ".$row->doc_name
               ];

          }

          $results = collect($formattedResults);
          $newRow = [];
          $newRow =[
              'special' => true,
              'ps_name' => NULL,
              'date'=> NULL,
              'prn_number'=> NULL,
              'amount' => number_format($results->sum('amount_new'),2),
              'ins_town'=> NULL,
              'act_dr_name'=> NULL,
          ];

          $formattedResults[] =$newRow;
        //   $row = [
        //     'special'=>true,
        //     'amount'=>number_format($formattedResults->sum(amount),2),

        // ];

        // $formattedResults->push($row);


          return[
            'results' =>$formattedResults,
               'count' => 0,

          ];
     }

     public function setColumns(ColumnController $columnController, Request $request){
          $columnController->text("ps_name")->setLabel("PS name");
          $columnController->text("date")->setLabel("Date");
          $columnController->text("prn_number")->setLabel("PRN number");
          $columnController->number("amount")->setLabel("Amount");
          $columnController->text("ins_town")->setLabel("Institution /town");
          $columnController->text("act_dr_name")->setLabel("Activity /Dr name");
     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setLink("team");
          $inputController->text("year")->setLabel("Financial Year");
          $inputController->date("month")->setLabel("Month");

          $inputController->setStructure([["team_id","month"]]);
     }
}
?>
