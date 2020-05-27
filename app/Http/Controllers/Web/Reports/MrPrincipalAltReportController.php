<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Traits\Territory;
use App\Models\UserArea;
use App\Models\UserTarget;
use App\Models\UserProductTarget;
use App\Form\Columns\ColumnController;
use function GuzzleHttp\json_decode;
use App\Models\TeamProduct;
use App\Models\Product;
use App\Models\Principal;
use App\Models\ReturnLine;
use App\Models\User;
use App\Models\UserCustomer;
use App\Models\Chemist;
use App\Models\Invoice;
use App\Exceptions\WebAPIException;
use App\Models\TeamUser;
use App\Models\InvoiceLine;

class MrPrincipalAltReportController extends ReportController{

     use Territory;
     protected $title = "MR & Principle Report";

     protected $results = [];

     public function search(Request $request){

          $values = $request->input('values');

          $final_year =strtotime($values['s_date']);

          $query =  DB::table('teams AS t')
                    ->select('t.tm_id','t.tm_name','tp.product_id','tu.u_id','p.product_code','p.product_name','u.name','u.id','u.divi_id','pr.principal_id','pr.principal_name')
                    ->join('team_users AS tu','tu.tm_id','t.tm_id')
                    ->join('team_products AS tp','tp.tm_id','t.tm_id')
                    ->join('product AS p','p.product_id','tp.product_id')
                    ->join('users AS u','u.id','tu.u_id')
                    ->join('principal AS pr','p.principal_id','pr.principal_id')
                    // ->where('u.divi_id',1)
                    ->whereNull('t.deleted_at')
                    ->whereNull('tu.deleted_at')
                    ->whereNull('tp.deleted_at')
                    ->whereNull('pr.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->orderBy('t.tm_id')
                    ->groupBy('u.id','p.product_id')
                    ->orderBy('u.id');

          if(!$request->has('values.div_id.value')){
               throw new WebAPIException('Division field is required');
          }

          if($request->has('values.team_id.value')){
               $query->where('t.tm_id',$request->input('values.team_id.value'));
          }

          if($request->has('values.ps_id.value')){
               $query->where('u.id',$request->input('values.ps_id.value'));
          }

          if($request->has('values.principle_id.value')){
               $query->where('p.principal_id',$request->input('values.principle_id.value'));
          }

          if($request->has('values.div_id.value')){
               $query->where('u.divi_id',$request->input('values.div_id.value'));
          }

          $count = $this->paginateAndCount($query,$request,'id');
          $results = $query->get();

          $period = $this->datePeriod($values['s_date'],$values['s_date']);

          $formatedresulst = [];

          $total_target_value = 0;
          $total_target_qty = 0;
          $total_achi_target_value = 0;
          $total_achi_target_qty = 0;
          $tot_achi_presantage = 0;


          $ytd_total_target_value = 0;
          $ytd_total_target_qty = 0;
          $ytd_total_achi_target_value = 0;
          $ytd_total_achi_target_qty = 0;
          $ytd_tot_achi_presantage = 0;

          $lastId = 0;

          $mtdResults = $this->initial($results->pluck('product_id'),$results->pluck('id'),date('Y-m-01',$final_year),date('Y-m-t',$final_year));
          $ytdResults = $this->initial($results->pluck('product_id'),$results->pluck('id'),date('Y-01-01',strtotime($values['s_date'])),date('Y-m-t',strtotime($values['s_date'])));

          foreach($results as $result){

               $ytdTargetValue = 0;
               $ytdTargetQty = 0;
               $value_av = 0;
               $ytd_value_av = 0;

               $ach_qty = 0;
               $ach_value = 0;

               $ytd_ach_val = 0;
               $ytd_ach_qty = 0;

               $year =  date('Y',strtotime($values['s_date']));
               $month = date('m',strtotime($values['s_date']));

               if($lastId!=$result->id){
                    $user = User::find($result->id);
               }

               $userProductTarget = $this->getTargetsForMonth($result->id,$result->product_id,$year,$month);

               if($lastId!=$result->id){
                    $towns = $this->getAllocatedTerritories($user);
               }

               $MonthAchievementProduct = $this->getAchievment($mtdResults,$towns->pluck('sub_twn_id')->all(),$result->product_id,$result->id);
               $ach_qty = round($MonthAchievementProduct['qty']);
               $ach_value = round($MonthAchievementProduct['amount'],2);

               if($MonthAchievementProduct['amount'] != 0 && $userProductTarget->upt_value != 0){
                    $value_av = round(($MonthAchievementProduct['amount']/$userProductTarget->upt_value)*100);
               }

               foreach ($period as $dt) {
                    $target = $this->getTargetsForMonth($result->id,$result->product_id,$dt->format('Y'),$dt->format('m'));

                    $ytdTargetQty += $target->upt_qty;
                    $ytdTargetValue += $target->upt_value;
               }

               $ytdAchi = $this->getAchievment($ytdResults,$towns->pluck('sub_twn_id')->all(),$result->product_id,$result->id);

               $ytd_ach_qty = round($ytdAchi['qty']);
               $ytd_ach_val = round($ytdAchi['amount'],2);


               if($ytd_ach_val != 0 && $ytdTargetValue != 0){
                    $ytd_value_av = round(($ytd_ach_val/$ytdTargetValue)*100);
               }

               $total_target_qty += $userProductTarget->upt_qty?$userProductTarget->upt_qty:0;
               $total_target_value += $userProductTarget->upt_value?$userProductTarget->upt_value:0;
               $total_achi_target_qty += $ach_qty ?round($ach_qty,2):0;
               $total_achi_target_value += $ach_value ?$ach_value:0;
               $tot_achi_presantage += $value_av;

               $ytd_total_target_qty += $ytdTargetQty;
               $ytd_total_target_value += $ytdTargetValue;
               $ytd_total_achi_target_qty += $ytd_ach_qty?$ytd_ach_qty:0;
               $ytd_total_achi_target_value += $ytd_ach_val?$ytd_ach_val:0;
               $ytd_tot_achi_presantage += $ytd_value_av;

               $formatedresulst [] = [
                    'team_name' => $result->tm_name,
                    'ps_name' => $result->name,
                    'principal' => $result->principal_name,
                    'pro_code' => $result->product_code,
                    'pro_name' => $result->product_name,
                    'target_qty' => $userProductTarget->upt_qty?$userProductTarget->upt_qty:0,
                    'target_value' => $userProductTarget->upt_value?number_format($userProductTarget->upt_value,2):0,
                    'achivement_qty' => $ach_qty ?round($ach_qty,2):0,
                    'achivement_value' => $ach_value ?number_format($ach_value,2):0,
                    'achievement_%' => $value_av,
                    'ytd_target_value' => number_format($ytdTargetValue,2),
                    'ytd_target_qty' => $ytdTargetQty,
                    'ytd_achivement_qty' => $ytd_ach_qty?$ytd_ach_qty:0,
                    'ytd_achievement_value' => $ytd_ach_val?number_format($ytd_ach_val,2):0,
                    'ytd_achivement_%' => $ytd_value_av
               ];

               $lastId = $result->id;
          }

          $formatedresulst [] = [
               'team_name' => 'Grand Total',
               'ps_name' => '',
               'principal' => '',
               'pro_code' => '',
               'pro_name' => '',
               'target_qty' => number_format($total_target_qty,2),
               'target_value' => number_format($total_target_value,2),
               'achivement_qty' => number_format($total_achi_target_qty,2),
               'achivement_value' => number_format($total_achi_target_value,2),
               'achievement_%' => $tot_achi_presantage,
               'ytd_target_value' => $ytd_total_target_qty,
               'ytd_target_qty' => number_format($ytd_total_target_value,2),
               'ytd_achivement_qty' => number_format($ytd_total_achi_target_qty,2),
               'ytd_achievement_value' => number_format($ytd_total_achi_target_value,2),
               'ytd_achivement_%' => number_format($ytd_tot_achi_presantage,2),
               'special' => true
          ];

          return[
               'count'=> $count,
               'results'=> $formatedresulst
          ];
     }

     protected function initial($products,$users,$fromDate,$toDate){
          $invoices = InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),DB::raw('sau.id'))
            ->join('product AS p','il.product_id','=','p.product_id')
            ->join('chemist AS c','c.chemist_id','il.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
               'sau.id',
               'st.sub_twn_id',
               'il.identity',
               'il.product_id',
               'p.product_code',
               'p.product_name',
               'p.principal_id',
               InvoiceLine::salesQtyColumn('gross_qty',false,false),
               InvoiceLine::salesQtyColumn('net_qty',false,false),
               InvoiceLine::salesAmountColumn('bdgt_value'),
               DB::raw('0 AS return_qty'),
               DB::raw('0 AS rt_bdgt_value'),
               DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price'),
               DB::raw('IF(ia.tia_qty IS NOT NULL OR sa.tsa_percent IS NOT NULL,1,0) AS sales_allocation')
            ])
            ->whereIn('sau.id',$users)
            ->whereIn('il.product_id',$products)
            ->whereDate('il.invoice_date','<=',$toDate)
            ->whereDate('il.invoice_date','>=',$fromDate)
            ->groupBy('il.product_id','sau.id','st.sub_twn_id','sales_allocation')
            ->get();

          $returns = InvoiceLine::bindSalesAllocation( DB::table('return_lines AS rl'),DB::raw('sau.id'),true)
            ->join('product AS p','rl.product_id','=','p.product_id')
            ->join('chemist AS c','c.chemist_id','rl.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->select([
               'sau.id',
               'st.sub_twn_id',
               'rl.identity',
               'rl.product_id',
               'p.product_code',
               'p.product_name',
               'p.principal_id',
               InvoiceLine::salesQtyColumn('return_qty',true,false),
               InvoiceLine::salesAmountColumn('rt_bdgt_value',true,false),
               DB::raw('0 AS gross_qty'),
               DB::raw('0 AS net_qty'),
               DB::raw('0 AS bdgt_value'),
               DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price'),
               DB::raw('IF(sa.tsa_percent IS NOT NULL,1,0) AS sales_allocation')
            ])
            ->whereIn('sau.id',$users)
            ->whereIn('rl.product_id',$products)
            ->whereDate('rl.invoice_date','<=',$toDate)
            ->whereDate('rl.invoice_date','>=',$fromDate)
            ->groupBy('rl.product_id','st.sub_twn_id','sau.id','sales_allocation')
            ->get();
          $results = $invoices->concat($returns);

          $results->transform(function($row){
               return [
                    'u_id'=>$row->id,
                    'sub_twn_id'=>$row->sub_twn_id,
                    'product_id'=>$row->product_id,
                    'qty'=>((float)$row->net_qty) - ((float)$row->return_qty),
                    'amount'=> ((float) $row->bdgt_value) - ((float)$row->rt_bdgt_value),
                    'principal_id'=>$row->principal_id,
                    'sales_allocation'=>$row->sales_allocation
               ];
          });

          return $results;

     }

     protected function getAchievment($results,$towns,$product,$userId){
          $ach  = 0;
          $qty = 0;


          foreach ($results as $key => $result) {
               if($result['u_id']==$userId&&(in_array($result['sub_twn_id'],$towns)||$result['sales_allocation'])&&$result['product_id']==$product){
                    $ach += $result['amount'];
                    $qty += $result['qty'];
               }
          }


          return ['amount'=>$ach,'qty'=>$qty];
     }

     protected function getTargetsForMonth($userId,$pro_id,$year,$month){

          $target = UserTarget::where('u_id',$userId)
               ->where('ut_month',$month)
               ->where('ut_year',$year)
               ->latest()
               ->first();

          if(!$target)
               return json_decode('{"upt_value":0,"upt_qty":0}');

          $user_product_target = UserProductTarget::where('ut_id',$target['ut_id'])
               ->where('product_id',$pro_id)
               ->select(DB::raw('SUM(upt_value) AS upt_value'),DB::raw('SUM(upt_qty) AS upt_qty'))
               ->first();

          return $user_product_target??json_decode('{ "upt_value":0,"upt_qty":0 }');
     }

     protected function setColumns(ColumnController $columnController,Request $request){

          $columnController->text('team_name')->setLabel("Team Name");
          $columnController->text('ps_name')->setLabel("PS Name");
          $columnController->text('principal')->setLabel("Principal");
          $columnController->text('pro_code')->setLabel("Product Code");
          $columnController->text('pro_name')->setLabel("Product Name");
          $columnController->text('target_qty')->setLabel("Taregt Qty");
          $columnController->text('target_value')->setLabel("Target Value");
          $columnController->text('achivement_qty')->setLabel("Acivement Qty");
          $columnController->text('achivement_value')->setLabel("Achievement Value");
          $columnController->text('achievement_%')->setLabel("Achievement %");
          $columnController->text('ytd_target_qty')->setLabel("YTD Taregt Qty");
          $columnController->text('ytd_target_value')->setLabel("YTD Taregt Value");
          $columnController->text('ytd_achivement_qty')->setLabel("YTD Achievement Qty");
          $columnController->text('ytd_achievement_value')->setLabel("YTD Achievement Value");
          // $columnController->text('ytd_achivement_%')->setLabel("YTD Achievement %");

     }

     protected function setInputs($inputController){

          $inputController->ajax_dropdown("ar_id")->setLabel("Area")->setLink("area")->setValidations('');
          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setWhere(['divi_id' => '{div_id}'])->setLink("team");
          $inputController->ajax_dropdown("div_id")->setLabel("Division")->setLink("division");
          $inputController->ajax_dropdown("principle_id")->setLabel("Principal")->setLink("principal")->setValidations('');
          $inputController->ajax_dropdown("ps_id")->setLabel("PS/MR Name")->setWhere(['u_tp_id'=>'3|2'.'|'.config('shl.product_specialist_type'),'tm_id' => '{team_id}'])->setLink("user")->setValidations('');
          $inputController->text("year")->setLabel("Year");
          $inputController->date("s_date")->setLabel("Month");

          $inputController->setStructure([
               ["div_id","team_id","ps_id"],["principle_id","ar_id","s_date"]
               ]);
     }
}
?>
