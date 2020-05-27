<?php
namespace App\Http\Controllers\Web\Reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\TeamUser;
use App\Models\UserTarget;
use App\Models\UserProductTarget;
use App\Traits\Territory;
use App\Models\User;
use App\Traits\Team;

class YtdTargetvsAchiReportController extends ReportController {

     use Team,Territory;
     protected $updateColumnsOnSearch = true;
     protected $title = "Ytd Target Vs Achievement Report";

     public function search(Request $request){
          $values = $request->input('values',[]);

          $query = DB::table('team_users as tu')
          ->select('tu.tm_id','u.name','u.id','tm_name','u.u_code','u.divi_id')
          ->join('users as u','u.id','tu.u_id')
          ->join('teams as t','t.tm_id','tu.tm_id')
          ->whereNull('u.deleted_at')
          ->whereNull('t.deleted_at')
          ->whereNull('tu.deleted_at');

          if(!isset($values['team_id']) && !isset($values['user_id'])){
               throw new WebAPIException('You have to select Team field or User field');
          }

          if(isset($values['divi_name'])){
               $query->where('u.divi_id',$values['divi_name']);
          }

          if(isset($values['team_id'])){
               $query->where('t.tm_id',$values['team_id']);
          }

          if(isset($values['user_id'])){
               $type = User::where('id',$values['user_id'])->first();
               if($type->u_tp_id == 2)
                    $query->where('t.fm_id',$values['user_id']);
               else if($type->u_tp_id == 3 || $type->u_tp_id==config('shl.product_specialist_type'))
                    $query->where('u.id',$values['user_id']);

          }

          $count = 0;
          $results = $query->get();

          $begin = new \DateTime(date('Y-m-d',strtotime($values['s_date'])));
          $end = new \DateTime(date('Y-m-d',strtotime($values['s_date'].'+1 year')));
          $interval = \DateInterval::createFromDateString('1 month');
          $period = new \DatePeriod($begin, $interval, $end);


          $results->transform(function($data) use($period,$values,$end){
               $user = User::find($data->id);
               $teamUser = TeamUser::where('u_id',$user->getKey())->latest()->first();
               $products = Product::getByUserForSales($user,['latestPriceInfo']);
               $towns = $this->getAllocatedTerritories($user);
               $YeaMonthsAchi = $this->makeQuery($towns,date('Y-01-01',strtotime($values['s_date'])),date('Y-m-t'),$teamUser?$teamUser->tm_id:0,$user?$user->getKey():0);

               $return['emp_no'] = $data->u_code;
               $return['name'] = $data->name;

               foreach ($period as $key => $month) {
                    $pro_user_target = 0;
                    $user_currentMonthAchi = 0;
                    $user_ytd_currentMonthAchi = 0;

                    $currentMonthAchievement = $this->makeQuery($towns,date('Y-m-d',strtotime($month->format('Y-m-d'))),date('Y-m-t',strtotime($month->format('Y-m-d').'+1 month')),$teamUser?$teamUser->tm_id:0,$user?$user->getKey():0);

                    foreach ($products as $key => $product) {

                         $user_target = $this->getTargetsForMonth($data->id,$product->product_id,/*date('Y')*/$values['year'],$month->format('m'));
                         $pro_user_target += $user_target->upt_value;

                         $currentMonthAchievementProduct = $currentMonthAchievement->where('product_id',$product->product_id)->first();
                         $user_currentMonthAchi += isset($currentMonthAchievementProduct)?$currentMonthAchievementProduct['amount']:0;

                         $ytdAchi = $YeaMonthsAchi->where('product_id',$product->product_id)->first();
                         $user_ytd_currentMonthAchi += isset($ytdAchi)?$ytdAchi['amount']:0;
                    }

                    $achi_presantage = 0;
                    if($user_currentMonthAchi !=0 && $pro_user_target != 0){
                         $achi_presantage = ($user_currentMonthAchi/$pro_user_target)*100;
                    }

                    $return['ori_target_val'.$month->format('m')] = number_format($pro_user_target,2);
                    $return['month_ach_val'.$month->format('m')] = number_format($user_currentMonthAchi,2);
                    $return['ach%'.$month->format('m')] = isset($achi_presantage)?number_format($achi_presantage,2):0;
               }

               $begin = new \DateTime(date('Y-01-01'));
               $end = new \DateTime(date('Y-m-t'));

               $interval = \DateInterval::createFromDateString('1 month');
               $period = new \DatePeriod($begin, $interval, $end);

               $ytdTarget = 0;
               foreach ($period as $key => $monthYtd) {
                    $user_target_ytd = $this->userYtdTarget($data->id,date('Y'),$monthYtd->format('m'));
                    $ytdTarget += $user_target_ytd->upt_value;
               }
               $return['ytd_tot_target_val'] = number_format($ytdTarget,2);
               $return['ytd_tot_ach_val'] = number_format($user_ytd_currentMonthAchi,2);

               $ytd_achi_presantage = 0;
               if($user_ytd_currentMonthAchi !=0 && $ytdTarget != 0){
                    $ytd_achi_presantage = ($user_ytd_currentMonthAchi/$ytdTarget)*100;
               }

               $return['ytd_tot_chi'] = number_format($ytd_achi_presantage,2);

               return $return;
          });

          $total['emp_no']= '';
          $total['special']= true;

           $results->push($total);

          return[
               'count'=> $count,
               'results'=> $results
          ];
     }

     protected function userYtdTarget($userId,$year,$month){

          $target = UserTarget::where('u_id',$userId)
               ->where('ut_month',$month)
               ->where('ut_year',$year)
               ->latest()
               ->first();

          if(!$target)
               return json_decode('{"upt_value":0,"upt_qty":0}');

          $user_product_target = UserProductTarget::where('ut_id',$target['ut_id'])
               ->select('upt_value','upt_qty')
               ->first();

          return $user_product_target??json_decode('{ "upt_value":0,"upt_qty":0 }');

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
               ->select('upt_value','upt_qty')
               ->first();

          return $user_product_target??json_decode('{ "upt_value":0,"upt_qty":0 }');
     }

     protected function makeQuery($towns,$fromDate,$toDate,$teamId,$userId){

          $invoices = InvoiceLine::whereWithSalesAllocation( InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$userId)
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
                InvoiceLine::salesAmountColumn('bdgt_value'),
                InvoiceLine::salesQtyColumn('gross_qty'),
                InvoiceLine::salesQtyColumn('net_qty'),
                DB::raw('0 AS return_qty'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
               ]),'c.sub_twn_id',$towns->pluck('sub_twn_id')->all())
            ->whereDate('il.invoice_date','<=',$toDate)
            ->whereDate('il.invoice_date','>=',$fromDate)
            ->groupBy('il.product_id')
            ->get();


        $returns = InvoiceLine::whereWithSalesAllocation( InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'),$userId,true)
            ->join('product AS p','rl.product_id','=','p.product_id')
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
                DB::raw('0 AS gross_qty'),
                InvoiceLine::salesAmountColumn('rt_bdgt_value',true),
                InvoiceLine::salesQtyColumn('return_qty',true),
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
                $netQty = 0;
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
                    'amount'=>round($netValue,2)
                ];
            });

        return $results;
     }

     protected function setColumns(ColumnController $columnController,Request $request){
          $values = $request->input('values',[]);
          $columnController->text('emp_no')->setLabel("Emp No");
          $columnController->text('name')->setLabel("Name");
          if(isset($values['s_date'])){

               $begin = new \DateTime(date('Y-m-d',strtotime($values['s_date'])));
               $end = new \DateTime(date('Y-m-d',strtotime($values['s_date'].'+1 year')));
               $interval = \DateInterval::createFromDateString('1 month');
               $period = new \DatePeriod($begin, $interval, $end);

               foreach ($period as $key => $month) {
                    $columnController->number('ori_target_val'.$month->format('m'))->setLabel($month->format('Y-M-d').' '."Original Target Value");
                    $columnController->number('month_ach_val'.$month->format('m'))->setLabel($month->format('Y-M-d').' '."Month Ach Value");
                    $columnController->number('ach%'.$month->format('m'))->setLabel($month->format('Y-M-d').' '."Ach%");
               }
          }

          $columnController->number('ytd_tot_target_val')->setLabel("YTD Total Target Value");
          $columnController->number('ytd_tot_ach_val')->setLabel("YTD Total Ach Value");
          $columnController->number('ytd_tot_chi')->setLabel("YTD Ach%");

     }

     protected function setInputs($inputController){

          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setLink("team");
          $inputController->ajax_dropdown("div_id")->setLabel("Division")->setWhere(['divi_id'=> 1])->setLink("division")->setValidations('');
          $inputController->ajax_dropdown("principle_id")->setLabel("Principal")->setLink("principal")->setValidations('');
          $inputController->ajax_dropdown("user_id")->setLabel("PS/MR & FM")->setWhere(['u_tp_id'=>'2|3'.'|'.config('shl.product_specialist_type'),'tm_id'=>'{team_id}'])->setLink("user");
          $inputController->date("s_date")->setLabel("Financial Month");
          $inputController->text("year")->setLabel("Financial Year");

          $inputController->setStructure([
               ["div_id","team_id","user_id"],["principle_id","year","s_date"]
               ]);
     }
}

?>
