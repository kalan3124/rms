<?php
namespace App\Http\Controllers\Web\Reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Form\Columns\ColumnController;
use App\Models\UserProductTarget;
use App\Models\UserTarget;
use App\Models\TeamUser;
use App\Models\Chemist;
use App\Models\UserCustomer;
use App\Models\User;
use App\Traits\Team;
use App\Traits\Territory;
use App\Exceptions\WebAPIException;
use App\Models\InvoiceLine;

class YtdMonthlySalesSheetController extends ReportController {

     use Team,Territory;
     protected $title = "Ytd Monthly Sales sheet Report";

     public function search(Request $request){
          $values = $request->input('values',[]);
          $sortBy = $request->input('sortBy');

          $result =  DB::table('teams AS t')
                    ->join('team_products AS tp','tp.tm_id','t.tm_id')
                    ->join('product AS p','tp.product_id','p.product_id')
                    ->select('t.tm_id','p.product_id','p.product_name')
                    ->whereNull('t.deleted_at')
                    ->whereNull('tp.deleted_at')
                    ->whereNull('p.deleted_at');
                    // ->groupBy('tp.product_id');

                    if(!isset($values['team'])){
                         throw new WebAPIException('Team field is required');
                    }

                    if(isset($values['team'])){
                         $result->where('tp.tm_id',$values['team']['value']);
                    }

                    $count = $this->paginateAndCount($result,$request,'t.tm_name');
                    $results = $result->get();

                    if(!isset($results)){
                         throw new WebAPIException('Data Not Found');
                    }
                    $formattedResults = [];

                    $month_target = 0;
                    foreach ($results as $key => $result) {

                         $value_av = 0;
                         $defict = 0;
                         $defict_value = 0;

                         $team_user = TeamUser::where('tm_id',$result->tm_id)->get();

                         $formattedResultsMonthly[] = $this->mainFunctionMonthly($team_user,$result,date("Y-m-01"),date("Y-m-t"),date('Y-m-01',strtotime('-1 year')),date('Y-m-t',strtotime('-1 year')),$value_av,$defict,$defict_value);

                         $formattedResultsYtd[] = $this->mainFunctionYTD($team_user,$result,date("Y-01-01"),date("Y-m-t"),date('Y-01-01',strtotime('-1 year')),date('Y-m-t',strtotime('-1 year')),$value_av,$defict,$defict_value);
                    }

          return [
               'count' =>$count,
               'results' => $formattedResultsMonthly,
               'results_ytd' => $formattedResultsYtd
          ];
     }

     protected function mainFunctionMonthly($team_user,$result,$from,$to,$ytdF,$ytdT,$value_av,$defict,$defict_value){
          $user_target_value = 0;
          $user_target_qty = 0;
          $user_ach_qty_month = 0;
          $user_ach_month = 0;
          $lastYear_user_ach_qty_month = 0;
          $lastYear_user_ach_month = 0;

               foreach($team_user as $tm_user){

                    $user = User::find($tm_user->u_id);

                    $towns = $this->getAllocatedTerritories($user);

                    $product_target = $this->getTargetsForMonth($tm_user->u_id,$result->product_id,date('Y'),date('m'));
                    $user_target_qty += $product_target->upt_qty;
                    $user_target_value += $product_target->upt_value;

                    $currentMonthAchievement = $this->makeQuery($towns,date('Y-m-01'),date('Y-m-t'),$tm_user->tm_id,$tm_user->u_id);
                    $currentMonthAchievementProduct = $currentMonthAchievement->where('product_id',$result->product_id)->first();
                    $user_ach_qty_month += $currentMonthAchievementProduct?$currentMonthAchievementProduct['qty']:0;
                    $user_ach_month += $currentMonthAchievementProduct?$currentMonthAchievementProduct['amount']:0;


                    $currentLastYearAchievement = $this->makeQuery($towns,date('Y-m-01',strtotime('-1 year')),date('Y-m-t',strtotime('-1 year')),$tm_user->tm_id,$tm_user->u_id);
                    $currentLastYearAchievementProduct = $currentLastYearAchievement->where('product_id',$result->product_id)->first();
                    $lastYear_user_ach_month += $currentLastYearAchievementProduct?$currentLastYearAchievementProduct['amount']:0;

                         if($user_ach_month != 0 && $user_target_value != 0){
                              $value_av = round(($user_ach_month/$user_target_value)*100);
                         }

                         if($user_target_qty !=0 && $user_ach_qty_month !=0){
                              $defict = $user_ach_qty_month - $user_target_qty;
                         }

                         if(isset($user_target_value) && isset($user_ach_month)){
                              $defict_value = $user_ach_month - $user_target_value;
                         }

                         $formattedResults  = [
                              'pro_id' => $result->product_name,
                              'target_qty' => isset($user_target_qty)?$user_target_qty:0,
                              'achiev_qty' => $user_ach_qty_month?$user_ach_qty_month:0,
                              'target_val' => isset($user_target_value)?number_format($user_target_value,2):'0.00',
                              'ach_val' => $user_ach_month?number_format($user_ach_month,2):0,
                              'ach_' => round($value_av,2),
                              'defict_qty' => $defict > 0 ? $defict:0,
                              'value' => $defict_value > 0 ? number_format($defict_value,2):'0.00',
                              'last_yearSameMonth' => isset($lastYear_user_ach_month)?number_format($lastYear_user_ach_month,2):'0.00',
                              'growth_' => 0,
                              // 'u_id' => $tm_user->u_id
                         ];
               }

               return $formattedResults;
     }
     protected function mainFunctionYTD($team_user,$result,$from,$to,$ytdF,$ytdT,$value_av,$defict,$defict_value){
          $product_target = 0;
          $product_tar_qty = 0;
          $product_tar_value = 0;
          $user_ach_qty_month = 0;
          $user_ach_month = 0;
          $lastYear_user_ach_qty_month = 0;
          $lastYear_user_ach_month = 0;
          foreach($team_user as $tm_user){

               $user = User::find($tm_user->u_id);

               $towns = $this->getAllocatedTerritories($user);

               $begin = new \DateTime(date('Y-01-01'));
               $end = new \DateTime(date('Y-m-d'));

               $interval = \DateInterval::createFromDateString('1 month');
               $period = new \DatePeriod($begin, $interval, $end);

               foreach($period as $dt){
                    $product_target = $this->getTargetsForMonth($tm_user->u_id,$result->product_id,date('Y'),$dt->format('m'));
                    $product_tar_qty += $product_target->upt_qty;
                    $product_tar_value += $product_target->upt_value;
               }

               $currentMonthAchievement = $this->makeQuery($towns,date('Y-01-01'),date('Y-m-t'),$tm_user->tm_id,$tm_user->u_id);
               $currentMonthAchievementProduct = $currentMonthAchievement->where('product_id',$result->product_id)->first();
               $user_ach_qty_month += $currentMonthAchievementProduct?$currentMonthAchievementProduct['qty']:0;
               $user_ach_month += $currentMonthAchievementProduct?$currentMonthAchievementProduct['amount']:0;


               $currentLastYearAchievement = $this->makeQuery($towns,date('Y-01-01',strtotime('-1 year')),date('Y-m-t',strtotime('-1 year')),$tm_user->tm_id,$tm_user->u_id);
               $currentLastYearAchievementProduct = $currentLastYearAchievement->where('product_id',$result->product_id)->first();
               $lastYear_user_ach_month += $currentLastYearAchievementProduct?$currentLastYearAchievementProduct['amount']:0;

               if($user_ach_month != 0 && $product_tar_qty != 0){
                    $value_av = round(($user_ach_month/$product_tar_qty)*100);
               }

               if($product_tar_qty !=0 && $user_ach_qty_month !=0){
                    $defict = $user_ach_qty_month - $product_tar_qty;
               }

               if(isset($product_tar_qty) && isset($user_ach_month)){
                    $defict_value = $user_ach_month - $product_tar_qty;
               }

               $formattedResults  = [
                    'pro_id' => $result->product_name,
                    'target_qty' => isset($product_tar_qty)?$product_tar_qty:0,
                    'achiev_qty' => $user_ach_qty_month?$user_ach_qty_month:0,
                    'target_val' => isset($product_tar_value)?round($product_tar_value,2):'0.00',
                    'ach_val' => $user_ach_month?number_format($user_ach_month,2):0,
                    'ach_' => round($value_av,2),
                    'defict_qty' => $defict > 0 ? $defict:0,
                    'value' => $defict_value > 0 ? round($defict_value,2):'0.00',
                    'last_yearSameMonth' => isset($lastYear_user_ach_month)?number_format($lastYear_user_ach_month,2):'0.00',
                    'growth_' => 0,
                    // 'u_id' => $tm_user->u_id
               ];
          }

          return $formattedResults;
     }

     protected function makeQuery($towns,$fromDate,$toDate,$teamId,$userId){

          $invoices = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$userId)
            ->join('product AS p','il.product_id','=','p.product_id')
            ->join('chemist AS c','c.chemist_id','il.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'il.identity',
                'il.product_id',
                'p.product_code',
                'p.product_name',
                'p.principal_id',
                InvoiceLine::salesQtyColumn('gross_qty'),
                InvoiceLine::salesQtyColumn('net_qty'),
                InvoiceLine::salesAmountColumn('bdgt_value'),
                DB::raw('0 AS return_qty'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
               ]),'c.sub_twn_id',$towns->pluck('sub_twn_id')->all())
            ->whereDate('il.invoice_date','<=',$toDate)
            ->whereDate('il.invoice_date','>=',$fromDate)
            ->groupBy('il.product_id')
            ->get();

        $returns = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'),$userId,true)
            ->join('product AS p','rl.product_id','=','p.product_id')
            // ->join('sub_town AS st','st.sub_twn_code','rl.city')
            // ->join('sub_town AS st','st.sub_twn_id','rl.sub_twn_id')
            ->join('chemist AS c','c.chemist_id','rl.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->select([
                'rl.identity',
                'rl.product_id',
                'p.product_code',
                'p.product_name',
                'p.principal_id',
                InvoiceLine::salesQtyColumn('return_qty',true),
                InvoiceLine::salesAmountColumn('rt_bdgt_value',true),
                DB::raw('0 AS gross_qty'),
                DB::raw('0 AS net_qty'),
                DB::raw('0 AS bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ]),'c.sub_twn_id',$towns->pluck('sub_twn_id')->all(),true)
            ->whereDate('rl.invoice_date','<=',$toDate)
            ->whereDate('rl.invoice_date','>=',$fromDate)
            ->groupBy('rl.product_id')
            ->get();

            $allProducts = $invoices->merge($returns);
            $allProducts->all();

            $allProducts = $allProducts->unique(function ($item) {
                return $item->product_code;
            });
            $allProducts->values()->all();

            $results = $allProducts->values();
            $results->all();

            $results->transform(function($row)use($results,$returns){
                $grossQty = 0;
                $netQty = 0;
                $rtnQty = 0;
                $netValue = 0;
                foreach ($results AS $inv){
                    if($row->product_id == $inv->product_id){
                        $netValue += $inv->bdgt_value;
                        $netQty += $inv->net_qty;
                    }
                }
                foreach ($returns AS $rtn){
                    if($row->product_id == $rtn->product_id){
                        $netValue -= $rtn->rt_bdgt_value;
                        $netQty -= $rtn->return_qty;
                    }
                }


                return [
                    'product_id'=>$row->product_id,
                    'qty'=>$netQty,
                    'amount'=>round($netValue,2),
                    'principal_id'=>$row->principal_id
                ];
            });

        return $results;
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
}
?>
