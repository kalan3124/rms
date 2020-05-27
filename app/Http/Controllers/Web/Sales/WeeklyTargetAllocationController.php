<?php
namespace App\Http\Controllers\Web\Sales;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;
use Illuminate\Support\Facades\DB;
use App\Models\SfaWeeklyTarget;

class WeeklyTargetAllocationController extends Controller{
     public function search(Request $request){

          $rep = $request->input('rep.value');
          $date = $request->input('month');
          if(!isset($rep))
               throw new WebAPIException("Sales Rep Field is required!!!");
               

          $check_weekly = SfaWeeklyTarget::where('u_id',$rep)
                    ->where('sfa_trg_year',date('Y',strtotime($request->input('month'))))
                    ->where('sfa_trg_month',date('m',strtotime($request->input('month'))))
                    ->get();
                   
          $check_weekly->transform(function($val){
               return[
                    'lastId' => $val->sfa_trg_week_no-1,
                    'start_week' => $val->week_start_date,
                    'end_week' => $val->week_end_date,
                    'value' => $val->trg_amount,
                    'week_presantage' => $val->percentage,
               ];
          });


          $target = SfaTarget::where('u_id',$request->input('rep.value'))
               ->where('trg_year',date('Y',strtotime($request->input('month'))))
               ->where('trg_month',date('m',strtotime($request->input('month'))))
               ->latest()
               ->first();


          $productTarget = DB::table('sfa_target_products')
                    ->select([
                         DB::raw('SUM(stp_amount) as value'),
                         DB::raw('SUM(stp_qty) as qty')
                    ])
                    ->where('sfa_trg_id',$target['sfa_trg_id'])
                    ->first();

          return [
               'totQty' => number_format($productTarget->qty,2),
               'totValue' => $productTarget->value,
               'totCurrent' => $productTarget->value,
               'ifCheckWeekly' => count($check_weekly) > 0?$check_weekly:null,
               'type' => count($check_weekly) > 0?false:true,
               'month_end' => date('t',strtotime($date))
          ];
     }

     public function saveTargets(Request $request){
          $targets =  $request->input('targets');
          $rep =  $request->input('rep.value');
          $month =  $request->input('month');
          $type =  $request->input('type');

          // return $targets;die;
          if(!isset($rep))
               throw new WebAPIException("Sales Rep Field is required!!!");

          $target = SfaTarget::where('u_id',$rep)
               ->where('trg_year',date('Y',strtotime($month)))
               ->where('trg_month',date('m',strtotime($month)))
               ->latest()
               ->first();

          if($type == true){
               $index = 0;
               foreach ($targets as $key => $value) {
                    $index++;
                    SfaWeeklyTarget::create([
                         'u_id' => $rep,
                         'sfa_trg_id' => $target['sfa_trg_id'],
                         'sfa_trg_year' => date('Y',strtotime($month)),
                         'sfa_trg_month' => date('m',strtotime($month)),
                         'sfa_trg_week_no' => $index,
                         'week_start_date' => $value['start_week'],
                         'week_end_date' => $value['end_week'],
                         'percentage' => $value['week_presantage'],
                         'trg_amount' => $value['value']
                    ]);
               }
     
               return [
                    'success'=>true,
                    'message'=>'Weekly Target Successfully Allocatted!!!'
               ];
          } else {
               foreach ($targets as $key => $value) {
                    $updateTarget = SfaWeeklyTarget::where('u_id',$rep)->where('sfa_trg_week_no',$key+1)->where('sfa_trg_year',date('Y',strtotime($month)))->where('sfa_trg_month',date('m',strtotime($month)))->first();
                    $updateTarget->week_start_date = $value['start_week'];
                    $updateTarget->week_end_date = $value['end_week'];
                    $updateTarget->percentage = $value['week_presantage'];
                    $updateTarget->trg_amount = $value['value'];
                    $updateTarget->save();
               }

               return [
                    'success'=>true,
                    'message'=>'Weekly Target Allocation Successfully Updated!!!'
               ];
          }
     }
}
?>