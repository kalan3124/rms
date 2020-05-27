<?php
namespace App\Http\Controllers\Web\Reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;
use App\Models\TeamUser;
use App\Models\UserTarget;
use App\Models\UserProductTarget;
use App\Traits\Territory;
use App\Traits\Team;
use App\Models\User;
use App\Models\UserCustomer;
use App\Models\Chemist;
use App\Models\InvoiceLine;

class YtdProductController extends ReportController {

     use Team,Territory;
     protected $updateColumnsOnSearch = true;
     protected $title = "Ytd Product Wise Sales Report";

     public function search(Request $request){
          $values = $request->input('values',[]);

          $query =  DB::table('teams AS t')
                    ->select('t.tm_id','t.tm_name','tp.product_id','tu.u_id','p.product_code','p.product_name','u.name','u.id','pr.principal_id','pr.principal_name')
                    ->join('team_users AS tu','tu.tm_id','t.tm_id')
                    ->join('team_products AS tp','tp.tm_id','t.tm_id')
                    ->join('product AS p','p.product_id','tp.product_id')
                    ->join('users AS u','u.id','tu.u_id')
                    ->join('principal AS pr','p.principal_id','pr.principal_id')
                    ->whereNull('t.deleted_at')
                    ->whereNull('tu.deleted_at')
                    ->whereNull('tp.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->groupBy('p.product_id');

                    if(!isset($values['team_id'])){
                         throw new WebAPIException('Team field is required');
                    }

                    if($request->has('values.team_id.value')){
                         $query->where('t.tm_id',$request->input('values.team_id.value'));
                    }

          $results = $query->get();

          $year = date('Y');
          $month = date('m');

          $begin = new \DateTime(date('Y-m-01',strtotime($values['s_date'])));
          $end = new \DateTime(date('Y-m-d',strtotime($values['e_date'])));

          $interval = \DateInterval::createFromDateString('1 month');
          $period = new \DatePeriod($begin, $interval, $end);

          $results->transform(function($result)use($period,$year){

               $user_target_value = 0;
               $user_target_qty = 0;
               $month_count = 0;
               $user_achi_tot = 0;

               $return['pro_name'] = $result->product_name;

               $team_user = TeamUser::where('tm_id',$result->tm_id)->get();
               foreach ($period as $key => $month) {
                    $total_value = 0;
                    foreach ($team_user as $key => $team) {

                         $month_date = $year.'-'.$month->format('m').'-'.date('01');

                         $product_target = $this->getTargetsForMonths($team->u_id,$result->product_id,$month->format('Y'),$month->format('m'));
                         $user_target_value += $product_target->upt_value;
                         $user_target_qty += $product_target->upt_qty;

                         $user = User::find($team->u_id);
                         $towns = $this->getAllocatedTerritories($user);

                         $ytd = $this->makeQuery($towns,$month_date,date('Y-m-t',strtotime($month->format('Y-m-d'))),$team->tm_id,$team->u_id);
                         $ytdProduct = $ytd->where('product_id',$result->product_id)->first();

                         $total_value += $ytdProduct?$ytdProduct['amount']:0;
                    }

                    if($total_value != 0)
                         $month_count++;

                    $return['month_'.$month->format('m')] = $total_value?number_format($total_value,2):0;
                    $user_achi_tot  += $total_value;
               }
               $return['value_qty_value'] = $user_target_qty." / ".$user_target_value;

               $current_month = $this->makeQuery($towns,date('Y-m-01'),date('Y-m-t'),$team->tm_id,$team->u_id);
               $current_month_Product = $current_month->where('product_id',$result->product_id)->first();
               $return['current_month_sales'] = $current_month_Product?round($current_month_Product['amount'],2):0;

               if($month_count != 0)
                    $avg = $user_achi_tot/$month_count;
               else
                    $avg = 0;

               $return['avg'] = round($avg,2);
               $return['growth'] = 0;

               return $return;
          });

          return[
               'count'=> 0,
               'results'=> $results
          ];
     }

     protected function getTargetsForMonths($userId,$pro_id,$year,$month){

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
                'p.principal_id',
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
                    'principal_id'=>$row->principal_id
               ];
          });

        return $results;
      }

     protected function setColumns(ColumnController $columnController,Request $request){
          $values = $request->input('values',[]);
          $columnController->text('pro_name')->setLabel("Product");

          if(isset($values['s_date']) && isset($values['e_date'])){

               $begin = new \DateTime(date('Y-m-01',strtotime($values['s_date'])));
               $end = new \DateTime(date('Y-m-d',strtotime($values['e_date'])));

               $interval = \DateInterval::createFromDateString('1 month');
               $period = new \DatePeriod($begin, $interval, $end);

               foreach ($period as $key => $month) {
                    $columnController->text('month_'.$month->format('m'))->setLabel($month->format('M'));
               }
          }

          $columnController->text('value_qty_value')->setLabel("QTY/Value");
          $columnController->text('avg')->setLabel("AVG");
          $columnController->text('current_month_sales')->setLabel("Current month sales");
          $columnController->text('growth')->setLabel("Growth % Compare to month");

     }

     protected function setInputs($inputController){

          $inputController->ajax_dropdown("fm_id")->setLabel("Field Manager")->setWhere([
               // 'tm_id'=>'{team_id}',
               'u_tp_id'=>2
          ])->setLink("user")->setValidations('');
          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setWhere([
               'fm_id'=>'{fm_id}'
          ])->setLink("team");
          $inputController->date("s_date")->setLabel("From");
          $inputController->date("e_date")->setLabel("To");

          $inputController->setStructure([
               ["team_id","fm_id"],
               ["s_date","e_date"]
               ]);
     }
}

?>
