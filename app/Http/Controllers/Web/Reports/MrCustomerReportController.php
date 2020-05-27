<?php
namespace App\Http\Controllers\Web\Reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use App\Models\Team;
use App\Traits\Territory;
use App\Traits\Team;
use App\Models\UserArea;
use App\Models\UserTarget;
use App\Models\UserProductTarget;
use App\Form\Columns\ColumnController;
use function GuzzleHttp\json_decode;
use App\Models\User;
use App\Exceptions\WebAPIException;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\TeamUser;

class MrCustomerReportController extends ReportController {

     use Territory,Team;

     protected $title = "Mr & Customer Report";
     protected $defaultSortColumn="tm_id";

     public function search(Request $request){

          $values = $request->input('values');

          $values = $request->input('values');

          $month = date('m-d',strtotime($values['s_date']));
          $final_year = $values['year'].'-'.$month;

          $next_year = date('Y-m-d',strtotime($final_year.'+1 year -1 day'));
          // return date('Y-m-d',strtotime($final_year));

          $query = DB::table('team_users as tu')
                    ->select('tu.tm_id','tu.u_id','u.id','u.name','t.tm_name','c.chemist_id','c.chemist_code','c.chemist_name')
                    ->join('teams as t','t.tm_id','tu.tm_id')
                    ->join('users as u','u.id','tu.u_id')
                    ->join('user_customer as uc','uc.u_id','u.id')
                    ->join('chemist as c', 'c.chemist_id','uc.chemist_id')
                    ->whereNotNull('uc.chemist_id')
                    ->whereNull('t.deleted_at')
                    ->whereNull('uc.deleted_at')
                    ->whereNull('c.deleted_at')
                    ->whereNull('u.deleted_at')
                    ->whereNull('tu.deleted_at');

          if(!$request->has('values.team_id.value')){
               throw new WebAPIException('Team field is required');
          }

          if($request->has('values.team_id.value')){
               $query->where('tu.tm_id',$request->input('values.team_id.value'));
          }

          if($request->has('values.ps_id.value')){
               $query->where('u.id',$request->input('values.ps_id.value'));
          }

          $count = $this->paginateAndCount($query,$request,'tm_id');
          $results = $query->get();
          // return $results;

          $formatedresulst = [];

          $results->transform(function($data){
               $data->team_name = $data->tm_id;
               $data->id = $data->id;
               return $data;
          });

          // $AchievementQty = 0;
          // $AchievementVal = 0;
          // $ytd_achi_tot_qty = 0;
          // $ytd_achi_tot_value = 0;
          foreach ($results as $key => $row) {
               $count_new = $results->where('team_name',$row->tm_id)->count();
               $count_user = $results->where('id',$row->id)->count();

               $user = User::find($row->id);
               $towns = $this->getAllocatedTerritories($user);

               if($key){
                    $tm_id = $row->tm_id;
                    $prevRow = $results[$key-1];
                    $prevTm_id = $prevRow->tm_id;

                    $id = $row->id;
                    $prevRow_id = $results[$key-1];
                    $prev_id = $prevRow_id->id;

                    if($tm_id != $prevTm_id){
                         $rowNew['team_name'] = $row->tm_name;
                         $rowNew['team_name_rowspan'] = $count_new;
                    } else {
                         $rowNew['team_name'] = null;
                         $rowNew['team_name_rowspan'] = 0;
                    }

                    if($id != $prev_id){
                         $rowNew['ps_name'] = $row->name;
                         $rowNew['ps_name_rowspan'] = $count_user;

                         $rowNew['target_qty'] = 0;
                         $rowNew['target_qty_rowspan'] = $count_user;

                         $rowNew['target_value'] = 0;
                         $rowNew['target_value_rowspan'] = $count_user;

                         $rowNew['ytd_target_qty'] = 0;
                         $rowNew['ytd_target_qty_rowspan'] = $count_user;

                         $rowNew['ytd_target_value'] = 0;
                         $rowNew['ytd_target_value_rowspan'] = $count_user;

                    } else {
                         $rowNew['ps_name'] = null;
                         $rowNew['ps_name_rowspan'] = 0;

                         $rowNew['target_qty'] = null;
                         $rowNew['target_qty_rowspan'] = 0;

                         $rowNew['target_value'] = null;
                         $rowNew['target_value_rowspan'] = 0;

                         $rowNew['ytd_target_qty'] = null;
                         $rowNew['ytd_target_qty_rowspan'] = 0;

                         $rowNew['ytd_target_value'] = null;
                         $rowNew['ytd_target_value_rowspan'] = 0;
                    }

               } else {
                    $rowNew['team_name'] = $row->tm_name;
                    $rowNew['team_name_rowspan'] = $count_new;

                    $rowNew['ps_name'] = $row->name;
                    $rowNew['ps_name_rowspan'] = $count_user;

                    $rowNew['target_qty'] = 0;
                    $rowNew['target_qty_rowspan'] = $count_user;

                    $rowNew['target_value'] = 0;
                    $rowNew['target_value_rowspan'] = $count_user;

                    $rowNew['ytd_target_qty'] = 0;
                    $rowNew['ytd_target_qty_rowspan'] = $count_user;

                    $rowNew['ytd_target_value'] = 0;
                    $rowNew['ytd_target_value_rowspan'] = $count_user;
               }

               $tot_qty = 0;
               $tot_value = 0;
               $ytd_tot_qty = 0;
               $ytd_tot_value = 0;
               $ach_pra_tot = 0;
               $AchievementQty = 0;
               $AchievementVal = 0;
               $ytd_achi_tot_qty = 0;
               $ytd_achi_tot_value = 0;

               $products = Product::getByUserForSales($user);
               $teamUser = TeamUser::where('u_id',$user->getKey())->latest()->first();

               $AchievementQuery = $this->makeQuery($towns,date('Y-m-d',strtotime($final_year)),date('Y-m-d',strtotime($next_year)) ,$teamUser?$teamUser->tm_id:0,$teamUser?$teamUser->u_id:0);
               $YTDAchievementQuery = $this->makeQuery($towns,date('Y-01-01'),date('Y-m-t'),$teamUser?$teamUser->tm_id:0,$teamUser?$teamUser->u_id:0);
               // return $AchievementQuery;

               foreach ($products as $key => $product) {
                    $userProductTarget = $this->getTargetsForMonth($row->id,$product->product_id,date('Y',strtotime($final_year)),date('m',strtotime($next_year)));
                    $tot_qty += $userProductTarget->upt_qty;
                    $tot_value += $userProductTarget->upt_value;

                    $AchievementProduct = $AchievementQuery->where('product_id',$product->product_id)->where('chemist_id',$row->chemist_id)->first();
                    $AchievementQty += $AchievementProduct['qty'];
                    $AchievementVal += $AchievementProduct['amount'];

                    $YTDAchievementProduct = $YTDAchievementQuery->where('product_id',$product->product_id)->where('chemist_id',$row->chemist_id)->first();
                    $ytd_achi_tot_qty += $YTDAchievementProduct['qty'];
                    $ytd_achi_tot_value += $YTDAchievementProduct['amount'];

               }

               $rowNew['team_name'] = $row->tm_name;
               $rowNew['ps_name'] = $row->name;
               $rowNew['cus_code'] = $row->chemist_code;
               $rowNew['cus_name'] = $row->chemist_name;
               $rowNew['target_qty'] = round($tot_qty);
               $rowNew['target_value'] = number_format($tot_value,2);
               $rowNew['achivement_qty'] = $AchievementQty?round($AchievementQty):0;
               $rowNew['achivement_value'] = $AchievementVal?number_format($AchievementVal,2):0.00;
               $rowNew['achievement_%'] = isset($ach_pra_tot)?$ach_pra_tot:0;
               $rowNew['ytd_target_qty'] = isset($ytd_tot_qty)?round($ytd_tot_qty):0;
               $rowNew['ytd_target_value'] = isset($ytd_tot_value)?number_format($ytd_tot_value,2):0.00;
               $rowNew['ytd_achivement_qty'] = isset($ytd_achi_tot_qty)?round($ytd_achi_tot_qty):0;
               $rowNew['ytd_achievement_value'] = isset($ytd_achi_tot_value)?number_format($ytd_achi_tot_value,2):0.00;
               $rowNew['ytd_achivement_%'] = 0;

               $formatedresulst[] = $rowNew;
          }


          return[
               'count'=> $count,
               'results'=> $formatedresulst
          ];
     }

     protected function makeQuery($towns,$fromDate,$toDate,$teamId,$userId){

          $invoices = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$userId)
            ->join('product AS p','il.product_id','=','p.product_id')
            // ->join('sub_town AS st','st.sub_twn_code','il.city')
            // ->join('sub_town AS st','st.sub_twn_id','il.sub_twn_id')
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
                'il.chemist_id',
                InvoiceLine::salesAmountColumn('bdgt_value'),
                InvoiceLine::salesQtyColumn('gross_qty'),
                InvoiceLine::salesQtyColumn('net_qty'),
               //  DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS gross_qty'),
                DB::raw('0 AS return_qty'),
               //  DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
               //  DB::raw('ROUND(IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * Ifnull(Sum(il.invoiced_qty), 0),2) AS bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
               ]),'il.city',$towns->pluck('sub_twn_code')->all())
            // ->whereIn('il.sub_twn_id',$towns->pluck('sub_twn_id')->all())
            ->whereDate('il.invoice_date','<=',$toDate)
            ->whereDate('il.invoice_date','>=',$fromDate)
            ->groupBy('il.product_id')
            ->get();


        $returns = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation( DB::table('return_lines AS rl'),$userId,true)
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
                'rl.chemist_id',
                InvoiceLine::salesAmountColumn('rt_bdgt_value',true),
                InvoiceLine::salesQtyColumn('return_qty',true),
                DB::raw('0 AS gross_qty'),
                DB::raw('0 AS net_qty'),
                DB::raw('0 AS bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
          ]),'rl.city',$towns->pluck('sub_twn_code')->all(),true)
            // ->whereIn('rl.sub_twn_id',$towns->pluck('sub_twn_id')->all())
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
                    'chemist_id'=> $row->chemist_id
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

     protected function setColumns(ColumnController $columnController,Request $request){
          $columnController->text('team_name')->setLabel("Team Name");
          $columnController->text('ps_name')->setLabel("PS Name");
          $columnController->text('cus_code')->setLabel("Customer Code");
          $columnController->text('cus_name')->setLabel("Customer Name");
          $columnController->text('target_qty')->setLabel("Taregt Qty");
          $columnController->text('target_value')->setLabel("Target Value");
          $columnController->text('achivement_qty')->setLabel("Acivement Qty");
          $columnController->text('achivement_value')->setLabel("Achievement Value");
          $columnController->text('achievement_%')->setLabel("Achievement %");
          $columnController->text('ytd_target_qty')->setLabel("YTD Taregt Qty");
          $columnController->text('ytd_target_value')->setLabel("YTD Taregt Value");
          $columnController->text('ytd_achivement_qty')->setLabel("YTD Achievement Qty");
          $columnController->text('ytd_achievement_value')->setLabel("YTD Achievement Value");
          $columnController->text('ytd_achivement_%')->setLabel("YTD Achievement %");
     }

     protected function setInputs($inputController){
          $inputController->ajax_dropdown("ar_id")->setLabel("Area")->setLink("area");
          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setLink("team");
          $inputController->ajax_dropdown("div_id")->setLabel("Division")->setLink("division");
          $inputController->ajax_dropdown("principle_id")->setLabel("Principal")->setLink("principal");
          $inputController->ajax_dropdown("ps_id")->setLabel("MR/PS Name")->setWhere(['u_tp_id'=>'3'.'|'.config('shl.product_specialist_type'),'tm_id'=>'{team_id}','divi_id'=>'{div_id}'])->setLink("user");
          $inputController->text("year")->setLabel("Financial Year");
          $inputController->date("s_date")->setLabel("Financial Month");

          $inputController->setStructure([
               ["ar_id","team_id","div_id"],["principle_id","ps_id","year","s_date"]
               ]);
     }
}
?>
