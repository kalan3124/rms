<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use App\Traits\Team;
use App\Models\UserTarget;
use App\Models\UserProductTarget;
use App\Traits\Territory;
use App\Models\User;
use App\Models\Chemist;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\UserCustomer;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\TeamUser;

class TargetVsAchievementReportController extends ReportController {
    use Team,Territory;

    protected $title = "Target Vs Acheivement Report";

    protected $defaultSortColumn="unpro_time";

    public function search(Request $request){
        $values = $request->input('values');

        $lastyear = date('Y',strtotime('- 1 year'));

        $validation = Validator::make($values,[
            'u_id'=>'required|array',
            'month'=>'required|date',
            'u_id.value'=>'required|exists:users,id'
        ]);

        if($validation->fails()){
            throw new WebAPIException('MR/PS field is required');
        }

        $user = User::find($values['u_id']['value']);
        $teamUser = TeamUser::where('u_id',$user->getKey())->latest()->first();

        $month = $values['month'];

        $products = Product::getByUserForSales($user,['latestPriceInfo']);

        $userTarget = UserTarget::where('u_id',$user->getKey())
            ->where('ut_year',date('Y',strtotime($month)))
            ->where('ut_month',date('m',strtotime($month)))
            ->latest()
            ->first();

        $LastYearSameMonthuserTarget = UserTarget::where('u_id',$user->getKey())
            ->where('ut_year',$lastyear)
            ->where('ut_month',date('m',strtotime($month)))
            ->latest()
            ->first();

        $userProductTargets = collect([]);
        $lastSameMonthuserProductTargets = collect([]);

        if($userTarget){
            $userProductTargets = UserProductTarget::whereIn('product_id',$products->pluck('product_id')->all())->where('ut_id',$userTarget->getKey())->get();
            if($LastYearSameMonthuserTarget){
            $lastSameMonthuserProductTargets = UserProductTarget::whereIn('product_id',$products->pluck('product_id')->all())->where('ut_id',$LastYearSameMonthuserTarget->getKey())->get();
            }
        }

        $results = [];

        $towns = $this->getAllocatedTerritories($user);

        $currentMonthAchievement = $this->makeQuery($towns,date('Y-m-01',strtotime($month)),date('Y-m-t',strtotime($month)),$teamUser?$teamUser->tm_id:0,$user->getKey());
        $lastYearThisMonth = $this->makeQuery($towns,date('Y-m-01',strtotime($month.' - 1 year')),date('Y-m-t',strtotime($month.' - 1 year')),$teamUser?$teamUser->tm_id:0,$user->getKey());
        $ytd = $this->makeQuery($towns,date('Y-01-01',strtotime($month)),date('Y-m-t',strtotime($month)),$teamUser?$teamUser->tm_id:0,$user->getKey());

        $tot_price = 0;
        $tot_cur_targ_qty = 0;
        $tot_cur_ach_qty = 0;
        $tot_cur_targ_val = 0;
        $tot_cur_ach_val = 0;
        $tot_lst_yr_cur_mnth_ach_qty = 0;
        $tot_lst_yr_cur_mnth_cur_ach_val =0;
        $tot_ytd_ach_qty = 0;
        $tot_ytd_cur_ach_val = 0;
        $ach_pra_tot = 0;
        $userProductTargetLastYearMonth = 0;

        foreach($products as $product){
            $achi_prasant = 0;
            $lastYearSameMonthPra = 0;

            $userProductTarget = $userProductTargets->where('product_id',$product->getKey())->first();
            $userProductTargetLastYearMonth = $lastSameMonthuserProductTargets->where('product_id',$product->getKey())->first();

            $currentMonthAchievementProduct = $currentMonthAchievement->where('product_id',$product->product_id)->first();

            $lastYearThisMonthProduct = $lastYearThisMonth->where('product_id',$product->product_id)->first();
            $ytdProduct = $ytd->where('product_id',$product->product_id)->first();

            $tot_price += $product&&$product->latestPriceInfo?($product->latestPriceInfo->lpi_bdgt_sales==0?$product->latestPriceInfo->lpi_pg01_sales:$product->latestPriceInfo->lpi_bdgt_sales):0;
            $tot_cur_targ_qty += $userProductTarget?$userProductTarget->upt_qty:0;
            $tot_cur_ach_qty += $currentMonthAchievementProduct?$currentMonthAchievementProduct['qty']:0;
            $tot_cur_targ_val += $userProductTarget?$userProductTarget->upt_value:0;
            $tot_cur_ach_val += $currentMonthAchievementProduct?$currentMonthAchievementProduct['amount']:0;
            $tot_lst_yr_cur_mnth_ach_qty += $lastYearThisMonthProduct?$lastYearThisMonthProduct['qty']:0;
            $tot_lst_yr_cur_mnth_cur_ach_val += $lastYearThisMonthProduct?$lastYearThisMonthProduct['amount']:0;
            $tot_ytd_ach_qty += $ytdProduct?$ytdProduct['qty']:0;
            $tot_ytd_cur_ach_val += $ytdProduct?$ytdProduct['amount']:0;

            if(isset($currentMonthAchievementProduct['amount']) && isset($userProductTarget->upt_value)&&$userProductTarget->upt_value>0){
                $achi_prasant =  ($currentMonthAchievementProduct['amount']/$userProductTarget->upt_value)*100 ;
                $ach_pra_tot += ($currentMonthAchievementProduct['amount']/$userProductTarget->upt_value)*100;
            }

            if(isset($lastYearThisMonthProduct['amount']) && isset($userProductTargetLastYearMonth->upt_value)&&$userProductTargetLastYearMonth->upt_value>0){
                $lastYearSameMonthPra = ($lastYearThisMonthProduct['amount']/$userProductTargetLastYearMonth->upt_value)*100;
            }

            $growth = $lastYearSameMonthPra - $achi_prasant;

            $results[]  = [
                'product_code'=>$product?$product->product_code:"",
                'product_name'=>$product?$product->product_name:"",
                // 'price'=>$product&&$product->latestPriceInfo?($product->latestPriceInfo->lpi_bdgt==0?$product->latestPriceInfo->lpi_pg_01:$product->latestPriceInfo->lpi_bdgt):0,
                'price'=>$product&&$product->latestPriceInfo?($product->latestPriceInfo->lpi_bdgt_sales==0?$product->latestPriceInfo->lpi_pg01_sales:$product->latestPriceInfo->lpi_bdgt_sales):0,
                'cur_targ_qty'=>number_format($userProductTarget?$userProductTarget->upt_qty:0),
                'cur_ach_qty'=>number_format($currentMonthAchievementProduct?$currentMonthAchievementProduct['qty']:0),
                'cur_targ_val'=>number_format($userProductTarget?$userProductTarget->upt_value:0,2),
                'cur_ach_val'=>number_format($currentMonthAchievementProduct?round($currentMonthAchievementProduct['amount'],2):0,2),
                'cur_ach_%' => round($achi_prasant,2),
                'lst_yr_cur_mnth_ach_qty'=>number_format($lastYearThisMonthProduct?$lastYearThisMonthProduct['qty']:0),
                'lst_yr_cur_mnth_cur_ach_val'=>number_format($lastYearThisMonthProduct?round($lastYearThisMonthProduct['amount'],2):0,2),
                'ytd_ach_qty'=>number_format($ytdProduct?$ytdProduct['qty']:0),
                'ytd_cur_ach_val'=>number_format($ytdProduct?round($ytdProduct['amount'],2):0,2),
                'growth'=>round($growth,2),
            ];
        }

        $results[] = [
            'product_code'=>'Page Total',
            'price'=> number_format($tot_price,2),
            'cur_targ_qty'=> number_format($tot_cur_targ_qty),
            'cur_ach_qty'=> number_format($tot_cur_ach_qty),
            'cur_targ_val'=> number_format($tot_cur_targ_val,2),
            'cur_ach_val'=> number_format($tot_cur_ach_val,2),
            'cur_ach_%' =>round($ach_pra_tot,2),
            'lst_yr_cur_mnth_ach_qty'=> number_format($tot_lst_yr_cur_mnth_ach_qty),
            'lst_yr_cur_mnth_cur_ach_val'=> number_format($tot_lst_yr_cur_mnth_cur_ach_val,2),
            'ytd_ach_qty'=> number_format($tot_ytd_ach_qty),
            'ytd_cur_ach_val'=> number_format($tot_ytd_cur_ach_val,2),
            'growth'=> 0,
            'special' => true
        ];

        return [
            'count'=>0,
            'results'=>$results
        ];

    }

    protected function getAdditionalHeaders($request)
    {
        return [
            [
                [
                    'title'=>'',
                    'colSpan'=>3
                ],
                [
                    'title'=>'Current Month',
                    'colSpan'=>5
                ],
                [
                    'title'=>'Last Year Current Motnth',
                    'colSpan'=>2
                ],
                [
                    'title'=>'YTD',
                    'colSpan'=>2
                ],
                [
                    'title'=>''
                ]
            ]
        ];
    }

    public function setColumns($columnController, Request $request){
        $columnController->text('product_code')->setLabel('Product Code')->isSearchable(false);
        $columnController->text('product_name')->setLabel('Product Name')->isSearchable(false);
        $columnController->text('price')->setLabel('Price')->isSearchable(false);
        $columnController->text('cur_targ_qty')->setLabel('Target Qty')->isSearchable(false);
        $columnController->text('cur_ach_qty')->setLabel('Achiev. Qty')->isSearchable(false);
        $columnController->text('cur_targ_val')->setLabel('Target Value Rs.')->isSearchable(false);
        $columnController->text('cur_ach_val')->setLabel('Achiev. Value Rs.')->isSearchable(false);
        $columnController->text('cur_ach_%')->setLabel('Achiev %.')->isSearchable(false);
        $columnController->text('lst_yr_cur_mnth_ach_qty')->setLabel('Achiev. Qty')->isSearchable(false);
        $columnController->text('lst_yr_cur_mnth_cur_ach_val')->setLabel('Achiev. Value Rs.')->isSearchable(false);
        $columnController->text('ytd_ach_qty')->setLabel('Achiev. Qty')->isSearchable(false);
        $columnController->text('ytd_cur_ach_val')->setLabel('Achiev. Value Rs.')->isSearchable(false);
        $columnController->text('growth')->setLabel('Growth %')->isSearchable(false);
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team')->setWhere([
            'divi_id'=>'{divi_id}'
        ])->setValidations('');
        $inputController->ajax_dropdown('divi_id')->setLabel("Division")->setLink("division")->setValidations('');
        $inputController->ajax_dropdown('u_id')->setLabel("PS/MR & FM")->setLink('user')->setWhere([
            'u_tp_id'=>'2|3'.'|'.config('shl.product_specialist_type'),
            "tm_id" => "{team}",
            'divi_id'=>'{divi_id}'
        ]);
        $inputController->date('month')->setLabel("Month")->setValidations('');
        $inputController->setStructure([
            ['divi_id','team'],
            ['u_id','month']
        ]);
    }

    protected function makeQuery($towns,$fromDate,$toDate,$teamId,$userId){

        $invoices =  InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$userId)
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
                InvoiceLine::salesAmountColumn('rt_bdgt_value',true),
                InvoiceLine::salesQtyColumn('return_qty',true),
                DB::raw('0 AS gross_qty'),
                DB::raw('0 AS net_qty'),
                DB::raw('0 AS bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
            ]),'c.sub_twn_id',$towns->pluck('sub_twn_id')->all(),true)
            // ->whereIn('rl.city',$towns->pluck('sub_twn_code')->all())
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
                    'amount'=>round($netValue,2)
                ];
            });

        return $results;
    }
}
