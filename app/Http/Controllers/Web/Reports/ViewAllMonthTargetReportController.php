<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use App\Models\DayType;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Traits\Team;
use App\Exceptions\WebAPIException;
use App\Models\UserTarget;
use App\Models\UserProductTarget;

class ViewAllMonthTargetReportController extends ReportController{
     use Team;
     protected $title = "Mr Monthly Target Report";

     public function search(Request $request){
          $values = $request->input('values',[]);

          if(!isset($values['u_id'])){
               throw new WebAPIException('Mr/Ps field is required');
          }

          $query =  DB::table('teams AS t')
                    ->select('t.tm_id','t.tm_name','tp.product_id','tu.u_id','p.product_code','p.product_name','u.name','u.id')
                    ->join('team_users AS tu','tu.tm_id','t.tm_id')
                    ->join('team_products AS tp','tp.tm_id','t.tm_id')
                    ->join('product AS p','p.product_id','tp.product_id')
                    ->join('users AS u','u.id','tu.u_id')
                    ->where('id',$values['u_id'])
                    ->whereNull('t.deleted_at')
                    ->whereNull('tu.deleted_at')
                    ->whereNull('tp.deleted_at')
                    ->groupBy('p.product_id');
                    
          $results = $query->get();

          $begin = new \DateTime(date('Y-01-01'));
          $end = new \DateTime(date('Y-m-d'));
          $interval = \DateInterval::createFromDateString('1 month');
          $period = new \DatePeriod($begin, $interval, $end);

          $results->transform(function($result) use($period){
               $return['product_code'] = $result->product_code;
               $return['product_name'] = $result->product_name;

               foreach ($period as $key => $month) {

                    $mainTarget = UserTarget::where('u_id', $result->id)
                              ->where('ut_month',$month->format('m'))
                              ->where('ut_year',$month->format('Y'))
                              ->latest()
                              ->first();   

                    $targetQty = 0;
                    $targetValue = 0;
                    if ($mainTarget) {
                         $productTargets = UserProductTarget::where('ut_id', $mainTarget->getKey())
                                        ->where('product_id',$result->product_id)
                                        ->first();

                         $targetQty = isset($productTargets)?$productTargets['upt_qty']:0;
                         $targetValue = isset($productTargets)?$productTargets['upt_value']:0;
                    }

                    $return['target_qty'.$month->format('m')] = $targetQty;
                    $return['target_value'.$month->format('m')] = $targetValue;
               }
               return $return;
          });

          $total['product_code'] = 'Total';
          $total['special'] = true;

          foreach ($period as $key => $month) {
               $total['target_qty'.$month->format('m')] = $results->sum('target_qty'.$month->format('m'));
               $total['target_value'.$month->format('m')] = number_format($results->sum('target_value'.$month->format('m')),2);
          }
          $results->push($total);

          return [
               'count' => 0,
               'results' => $results
          ];
     }

     // protected function getAdditionalHeaders($request){

     //      $first_row = [
     //           "title"=>"",
     //           "colSpan"=>2
     //      ];

     //      $second_row = [
     //           "title"=> "Month",
     //           "colSpan"=>2
     //      ];

     //      $columns = [[
     //           $first_row,
     //           $second_row
     //      ]];
          
     //      return $columns;
     // }

     public function setColumns($columnController, Request $request){
          $columnController->text('product_code')->setLabel("Product Code");
          $columnController->text('product_name')->setLabel("Product Name");

          $begin = new \DateTime(date('Y-01-01'));
          $end = new \DateTime(date('Y-m-d'));
          $interval = \DateInterval::createFromDateString('1 month');
          $period = new \DatePeriod($begin, $interval, $end);

          foreach ($period as $key => $month) {
               $columnController->number('target_qty'.$month->format('m'))->setLabel($month->format('M')." - Target Qty");
               $columnController->number('target_value'.$month->format('m'))->setLabel($month->format('M')." - Target Value");       
          } 
     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown('u_id')->setLabel('MR/PS')->setLink('user')->setWhere([
          'u_tp_id'=>'3'.'|'.config('shl.product_specialist_type')
          ]);

          $inputController->setStructure([
          ['u_id'],
          ]);
     }
}
?>