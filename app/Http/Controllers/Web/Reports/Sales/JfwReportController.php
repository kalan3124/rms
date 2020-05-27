<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SalesItineraryDateJFW;
use App\Exceptions\WebAPIException;
use App\Models\SfaSalesOrder;
use App\Models\SfaSrItineraryDetail;
use App\Models\Route;
use App\Models\InvoiceLine;

class JfwReportController extends ReportController{
     protected $title = "JFW Report";

     protected $updateColumnsOnSearch = true;

     public function search(Request $request){
          $values = $request->input('values');

          if(!isset($values['user'])){
               throw new WebAPIException('User field is required');
          }

          $begin = new \DateTime(date('Y-m-01',strtotime($values['month'])));
          $end = new \DateTime(date('Y-m-t',strtotime($values['month'])));
          $end = $end->modify('1 day');

          $interval = \DateInterval::createFromDateString('1 day');
          $period = new \DatePeriod($begin, $interval, $end);

          $jfw = SalesItineraryDateJFW::where('u_id',$values['user'])->get();

          $formattedResults = [];

          foreach ($period as $key => $dt) {

               $val = DB::table('sfa_itinerary as si')
                    ->join('sfa_itinerary_date as sid','si.s_i_id','sid.s_i_id')
                    ->join('users as u','u.id','si.u_id')
                    ->join('sfa_route as sr','sr.route_id','sid.route_id')
                    ->join('area as ar','sr.ar_id','ar.ar_id')
                    ->whereIn('sid.s_id_id',$jfw->pluck('s_id_id')->all())
                    ->where('s_i_year',date('Y',strtotime($values['month'])))
                    ->where('s_i_month',date('m',strtotime($values['month'])))
                    ->where('s_id_date',$dt->format('d'))
                    ->whereNull('sr.deleted_at')
                    ->whereNull('u.deleted_at')
                    ->whereNull('sid.deleted_at')
                    ->whereNull('si.deleted_at');

                    if(isset($values['area'])){
                         $val->where('sr.ar_id',$values['area']);
                    }

               $results = $val->first();
               $sale_actuals = 0;
               $sh_call = 0;
               $worked_route = "";
               $sale_ac = 0;
               if(isset($results->id)){
                    $sale_ac = SfaSalesOrder::where('u_id',$results->id)->whereDate('order_date',$dt->format('Y-m-d'))->count();
                    $sh_call = SfaSrItineraryDetail::where('sr_id',$results->id)->where('route_id',$results->route_id)->where('sr_i_year',date('Y',strtotime($values['month'])))->where('sr_i_month',date('m',strtotime($values['month'])))->where('sr_i_date',$dt->format('d'))->latest()->first();

                    if(isset($sh_call->route_id)){
                         $worked_route = Route::where('route_id',$sh_call->route_id)->first();
                    }
                    $sale_actuals = SfaSalesOrder::where('u_id',$results->id)->whereDate('order_date',$dt->format('Y-m-d'))->get();
                    $sale_act_new = InvoiceLine::whereIn('order_no',$sale_actuals->pluck('order_no')->all())->get();

                    $sale_actuals_tot = 0;
                    foreach ($sale_act_new as $key => $val) {
                         $sale_actuals_tot += $val->net_curr_amount?$val->net_curr_amount:0;
                    }
               }

               $formattedResults[] = [
                    'date' => $dt->format('M-d'),
                    'work_with' => isset($results->name)?$results->name:'-',
                    'route_as' => isset($results->route_name)?$results->route_name:'-',
                    'worked_route' => isset($worked_route->route_name)?$worked_route->route_name:'-',
                    'sc' => isset($sh_call->outlet_count)?$sh_call->outlet_count:0,
                    'ac' => isset($sale_ac)?$sale_ac:0,
                    'asm_visit' => 0,
                    'sale_target' => 0,
                    'sale_actuals' => isset($sale_actuals_tot)?number_format($sale_actuals_tot,2):0,
                    'feedback' => '-',
                    'feedback_on_trade' => '-'
               ];

            //    $newRow = [];
            //    $newRow  = [
            //        'special' => true,
            //        'sale_target' => number_format($formattedResults->sum('sale_target'),2)

            //    ];

            //    $formattedResults->push($newRow);
          }



          return[
               'results' => $formattedResults,
               'count' => 0
          ];
     }

     public function setColumns(ColumnController $columnController, Request $request){

          $columnController->text('date')->setLabel('Date');
          $columnController->text('work_with')->setLabel('Worked with (SR Name)');
          $columnController->text('route_as')->setLabel('Ruote as per Itinirary');
          $columnController->text('worked_route')->setLabel('Route /Town(s) worked');

          $columnController->number('sc')->setLabel('S/C');
          $columnController->number('ac')->setLabel('A/C');
          $columnController->number('asm_visit')->setLabel('ASM VISIT');
          $columnController->number('sale_target')->setLabel('Sales Target');
          $columnController->number('sale_actuals')->setLabel('Sales Actuals');

          $columnController->text('feedback')->setLabel('Feedback on SR');
          $columnController->text('feedback_on_trade')->setLabel('Feedback on Trade (Customer/Competitor Activities etc)');

     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown('area')->setLabel('Area')->setLink('area')->setValidations('');
          $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id'=>'13'])->setValidations('');
          $inputController->date('month')->setLabel('Month')->setLink('month');

          $inputController->setStructure([
               ['area','user','month']
          ]);
     }
}
?>
