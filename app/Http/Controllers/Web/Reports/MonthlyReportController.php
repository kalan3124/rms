<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Form\Columns\ColumnController;
use App\Traits\Territory;
use App\Traits\Team;
use App\Models\User;
use App\Models\UserProductTarget;
use App\Models\UserTarget;
use App\CSV\UserCustomer;
use App\Models\UserCustomer as AppUserCustomer;
use App\Exceptions\WebAPIException;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\TeamUser;

class MonthlyReportController extends ReportController{
     use Territory,Team;
     protected $title = "Monthly Report";

     public function search(Request $request){
          $values = $request->input('values');

          $query =  DB::table('teams AS t')
                    ->select('t.tm_id','t.tm_name','tp.product_id','tu.u_id','p.product_code','p.product_name','u.name','u.id','u.divi_id','pr.principal_id','pr.principal_name','uc.chemist_id','c.chemist_code','c.chemist_name')
                    ->join('team_users AS tu','tu.tm_id','t.tm_id')
                    ->join('team_products AS tp','tp.tm_id','t.tm_id')
                    ->join('product AS p','p.product_id','tp.product_id')
                    ->join('users AS u','u.id','tu.u_id')
                    ->join('principal AS pr','pr.principal_id','p.principal_id')
                    ->join('user_customer as uc','uc.u_id','u.id')
                    ->join('chemist as c', 'c.chemist_id','uc.chemist_id')
                    // ->where('u.divi_id',1)
                    ->whereNull('uc.deleted_at')
                    ->whereNull('t.deleted_at')
                    ->whereNull('tu.deleted_at')
                    ->whereNull('tp.deleted_at')
                    ->whereNull('p.deleted_at')
                    ->whereNull('c.deleted_at')
                    ->whereNull('pr.deleted_at')
                    ->orderBy('product_code','ASC');

          if(!$request->has('values.team.value'))
               throw new WebAPIException("Team field is required");

          if($request->has('values.team.value')){
               $query->where('t.tm_id',$request->input('values.team.value'));
          }

          if($request->has('values.user.value')){
               $query->where('u.id',$request->input('values.user.value'));
          }

          $count = $this->paginateAndCount($query,$request,'id');
          $results = $query->get();

          $results->transform(function($data){
               $data->team_name = $data->tm_id;
               $data->id = $data->id;
               $data->product_id = $data->product_id;
               return $data;
          });

          $formattedResults = [];

          $begin = new \DateTime(date('Y-m-d',strtotime($values['year'])));
          // $begin = new \DateTime(date('Y-01-01',strtotime($values['year'])));
          $end = new \DateTime(date('Y-m-d',strtotime($values['year'].'+1 year')));
          // $end = new \DateTime(date('Y-m-t'));
          $interval = \DateInterval::createFromDateString('1 month');
          $period = new \DatePeriod($begin, $interval, $end);

          foreach ($results as $key => $row) {
               $count_new = $results->where('team_name',$row->tm_id)->count();
               $count_user = $results->where('id',$row->id)->count();
               $count_pro = $results->where('product_id',$row->product_id)->count();
               $user = User::find($row->id);
               $teamUser = TeamUser::where('u_id',$user->getKey())->latest()->first();
               // $towns = $this->getAllocatedTerritories($user);

               try{
                    $towns = $this->getAllocatedTerritories($user);
               } catch(\Throwable $e){
                    $towns = collect();
               }

               $areas_new = $towns->unique('ar_id');

               $areas_new->transform(function($area){
                    return $area->ar_name;
               });

               $areaNames = implode(', ',$areas_new->all());

               if($key){
                    $tm_id = $row->tm_id;
                    $prevRow = $results[$key-1];
                    $prevTm_id = $prevRow->tm_id;

                    if($tm_id != $prevTm_id){
                         $rowNew['team_name'] = $row->tm_name;
                         $rowNew['team_name_rowspan'] = $count_new;
                    } else {
                         $rowNew['team_name'] = null;
                         $rowNew['team_name_rowspan'] = 0;
                    }

               } else {
                    $rowNew['team_name'] = $row->tm_name;
                    $rowNew['team_name_rowspan'] = $count_new;
               }

               if($key){
                    $id = $row->id;
                    $prevRow = $results[$key-1];
                    $previd = $prevRow->id;

                    if($id != $previd){
                         $rowNew['ps_name'] = $row->name;
                         $rowNew['ps_name_rowspan'] = $count_user;

                         $rowNew['town_name'] =  $areaNames;
                         $rowNew['town_name_rowspan'] = $count_user;

                         $rowNew['target_qty'] = 0;
                         $rowNew['target_qty_rowspan'] = $count_user;

                         $rowNew['target_value'] = 0;
                         $rowNew['target_value_rowspan'] = $count_user;

                         $rowNew['ytd_tar_qty'] = 0;
                         $rowNew['ytd_tar_qty_rowspan'] = $count_user;

                         $rowNew['ytd_tar_val'] = 0;
                         $rowNew['ytd_tar_val_rowspan'] = $count_user;

                    } else {
                         $rowNew['ps_name'] = null;
                         $rowNew['ps_name_rowspan'] = 0;

                         $rowNew['town_name'] = null;
                         $rowNew['town_name_rowspan'] = 0;

                         $rowNew['target_qty'] = null;
                         $rowNew['target_qty_rowspan'] = 0;

                         $rowNew['target_value'] = null;
                         $rowNew['target_value_rowspan'] = 0;

                         $rowNew['ytd_tar_qty'] = null;
                         $rowNew['ytd_tar_qty_rowspan'] = 0;

                         $rowNew['ytd_tar_val'] = null;
                         $rowNew['ytd_tar_val_rowspan'] = 0;
                    }
               } else {
                    $rowNew['ps_name'] = $row->name;
                    $rowNew['ps_name_rowspan'] = $count_user;

                    $rowNew['town_name'] = $areaNames;
                    $rowNew['town_name_rowspan'] = $count_user;

                    $rowNew['target_qty'] = 0;
                    $rowNew['target_qty_rowspan'] = $count_user;

                    $rowNew['target_value'] = 0;
                    $rowNew['target_value_rowspan'] = $count_user;

                    $rowNew['ytd_tar_qty'] = 0;
                    $rowNew['ytd_tar_qty_rowspan'] = $count_user;

                    $rowNew['ytd_tar_val'] = 0;
                    $rowNew['ytd_tar_val_rowspan'] = $count_user;
               }

               if($key){
                    $product_id = $row->product_id;
                    $prevRow = $results[$key-1];
                    $prevProduct_id = $prevRow->product_id;

                    if($product_id != $prevProduct_id){
                         $rowNew['pro_code'] = $row->product_id;
                         $rowNew['pro_code_rowspan'] = $count_pro;

                         $rowNew['pro_name'] = $row->product_name;
                         $rowNew['pro_name_rowspan'] = $count_pro;

                         $rowNew['principal'] = $row->principal_name;
                         $rowNew['principal_rowspan'] = $count_pro;
                    } else {
                         $rowNew['pro_code'] = null;
                         $rowNew['pro_code_rowspan'] = 0;

                         $rowNew['pro_name'] = null;
                         $rowNew['pro_name_rowspan'] = 0;

                         $rowNew['principal'] = null;
                         $rowNew['principal_rowspan'] = 0;
                    }

               } else {
                    $rowNew['pro_code'] = $row->product_id;
                    $rowNew['pro_code_rowspan'] = $count_pro;

                    $rowNew['pro_name'] = $row->product_name;
                    $rowNew['pro_name_rowspan'] = $count_pro;

                    $rowNew['principal'] = $row->principal_name;
                    $rowNew['principal_rowspan'] = $count_pro;
               }

               $rowNew['ps_name'] = $row->name;
               $rowNew['team_name'] = $row->tm_name;
               $rowNew['principal'] = $row->principal_name;
               $rowNew['pro_code'] = $row->product_code;
               $rowNew['pro_name'] = $row->product_name;

               $rowNew['cus_code'] = $row->chemist_code;
               $rowNew['cus_name'] = $row->chemist_name;

               $rowNew['town_name'] = $areaNames;

               $products = Product::getByUserForSales($user);

               $ytd = $this->makeQuery($towns,date('Y-01-01',strtotime($values['month'])),date('Y-m-t',strtotime($values['month'])),$teamUser?$teamUser->tm_id:0,$teamUser?$teamUser->u_id:0);
               $monthlyAchi = $this->makeQuery($towns,date('Y-m-01',strtotime($values['month'])),date('Y-m-t',strtotime($values['month'])),$teamUser?$teamUser->tm_id:0,$teamUser?$teamUser->u_id:0);

               $tot_qty = 0;
               $tot_value = 0;
               $ytd_achi_tot_qty = 0;
               $ytd_achi_tot_value = 0;
               $ytd_target_qty = 0;
               $ytd_target_val = 0;

               $ytd_achi_month_tot_qty = 0;
               $ytd_achi_month_tot_value = 0;

               // $ytdProduct = $ytd->where('product_id',$row->product_id)->where('chemist_id',$row->chemist_id)->first();
               // $ytd_achi_tot_qty = $ytdProduct['qty'];
               // $ytd_achi_tot_value = $ytdProduct['amount'];

               // $monthlyAchiProduct = $monthlyAchi->where('product_id',$row->product_id)->where('chemist_id',$row->chemist_id)->first();
               // $ytd_achi_month_tot_qty = $monthlyAchiProduct['qty'];
               // $ytd_achi_month_tot_value = $monthlyAchiProduct['amount'];

               foreach ($products as $key => $product) {
                    $userProductTarget = $this->getTargetsForMonth($row->id,$product->product_id,date('Y',strtotime($values['year'])),date('m',strtotime($values['month'])));
                    $tot_qty += $userProductTarget->upt_qty;
                    $tot_value += $userProductTarget->upt_value;

                    $ytdProduct = $ytd->where('product_id',$product->product_id)->where('chemist_id',$row->chemist_id)->first();
                    $ytd_achi_tot_qty += $ytdProduct['qty'];
                    $ytd_achi_tot_value += $ytdProduct['amount'];

                    $monthlyAchiProduct = $monthlyAchi->where('product_id',$product->product_id)->where('chemist_id',$row->chemist_id)->first();
                    $ytd_achi_month_tot_qty += $monthlyAchiProduct['qty'];
                    $ytd_achi_month_tot_value += $monthlyAchiProduct['amount'];

                    foreach ($period as $key => $month) {
                         $userProductTarget = $this->getTargetsForMonth($row->id,$product->product_id,$month->format('Y'),$month->format('m'));
                         $ytd_target_qty += $userProductTarget->upt_qty;
                         $ytd_target_val += $userProductTarget->upt_value;
                    }
               }

               $rowNew['target_qty'] = isset($tot_qty)?$tot_qty:0;
               $rowNew['target_value'] = isset($tot_value)?number_format($tot_value,2):0.00;

               $rowNew['achi_qty'] = isset($ytd_achi_month_tot_qty)?$ytd_achi_month_tot_qty:0;
               $rowNew['achi_val'] = isset($ytd_achi_month_tot_value)?$ytd_achi_month_tot_value:0;

               $rowNew['achi_%'] = 0;

               $rowNew['ytd_tar_qty'] = isset($ytd_target_qty)?$ytd_target_qty:0;
               $rowNew['ytd_tar_val'] = isset($ytd_target_val)?number_format($ytd_target_val,2):0;

               $rowNew['ytd_ach_qty'] = isset($ytd_achi_tot_qty)?$ytd_achi_tot_qty:0;
               $rowNew['ytd_ach_val'] = isset($ytd_achi_tot_value)?number_format($ytd_achi_tot_value,2):0;

               $rowNew['ytd_ach_%'] = 0;

               $formattedResults[] = $rowNew;
          }

          $formattedResults[] = [
               'special' => true
          ];

          return [
               'count'=>$count,
               'results'=>$formattedResults
          ];
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
                'il.chemist_id',
                InvoiceLine::salesAmountColumn('bdgt_value'),
                InvoiceLine::salesQtyColumn('gross_qty'),
                InvoiceLine::salesQtyColumn('net_qty'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ]),'il.city',$towns->pluck('sub_twn_code')->all())
            ->whereDate('il.invoice_date','<=',$toDate)
            ->whereDate('il.invoice_date','>=',$fromDate)
            ->groupBy('il.product_id')
            ->get();


        $returns = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'),$userId,true)
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
     public function setColumns(ColumnController $columnController, Request $request){
          $columnController->text("team_name")->setLabel("Team Name");
          $columnController->text("ps_name")->setLabel("Ps Name");
          $columnController->text("principal")->setLabel("Principal");
          $columnController->text("pro_code")->setLabel("Product Code");
          $columnController->text("pro_name")->setLabel("Product Name");
          $columnController->text("town_name")->setLabel("Town Name");
          $columnController->text("cus_code")->setLabel("Customer Code");
          $columnController->text('cus_name')->setLabel("Customer Name");
          $columnController->number('target_qty')->setLabel("Taregt Qty");
          $columnController->number('target_value')->setLabel("Target Value");
          $columnController->number('achi_qty')->setLabel("Acivement Qty");
          $columnController->number("achi_val")->setLabel("Achievement Value");
          $columnController->number("achi_%")->setLabel("Achievement %");
          $columnController->number("ytd_tar_qty")->setLabel("YTD Taregt Qty");
          $columnController->number("ytd_tar_val")->setLabel("YTD Target Value");
          $columnController->number("ytd_ach_qty")->setLabel("YTD Achievement Qty");
          $columnController->number("ytd_ach_val")->setLabel("YTD Achievement Value");
          $columnController->number("ytd_ach_%")->setLabel("YTD Achievement %");
     }

     public function setInputs($inputController){
     $inputController->ajax_dropdown("divi_id")->setLabel("Division")->setLink("division")->setValidations('');
     $inputController->ajax_dropdown("pri_id")->setLabel("Principle")->setLink("principal")->setValidations('');
     $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team');
     $inputController->ajax_dropdown("user")->setWhere(['tm_id'=>"{team}"])->setLabel("PS/MR or FM")->setLink("user")->setValidations('');
     $inputController->text("year")->setLabel("Financial Year");
     $inputController->date("month")->setLabel("Financial Month");

     $inputController->setStructure([["divi_id","team","user"],["pri_id","year","month"]]);
     }
}
?>
