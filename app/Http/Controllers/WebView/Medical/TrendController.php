<?php
namespace App\Http\Controllers\WebView\Medical;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Ext\InvoiceHead;
use App\Models\SubTown;
use App\Models\Chemist;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\TeamUser;
use App\Traits\Territory;
use App\Traits\Team;

class TrendController extends Controller
{
    use Territory,Team;

    public function getTrends(){
        $user= Auth::user();

        $allocatedTowns = $this->getAllocatedTerritoriesForSales($user);

        $allocatedSubTownIds = [];

        $allocatedSubTownIds = $allocatedTowns->pluck('sub_twn_id')->all();
        $allocatedSubTownCodes = $allocatedTowns->pluck('sub_twn_code');

        // Team products for sales rep
        $teamProducts = $this->getProductsByUser($user);
        $teamProducts->transform(function($product){
            return [
                'product_id'=>$product->getKey(),
                'product_code'=>$product->product_code,
                'product_name'=>$product->product_name,
                'amount'=>0,
                'qty'=>0
            ];
        });

        $products = Product::appendBudgetPrice($teamProducts,"budget_price","product_code");

        $chemists = Chemist::whereIn('sub_twn_id',$allocatedSubTownIds)->get();

        $teamId = 0;

        $teamUser = TeamUser::where('u_id',$user->getKey())->latest()->first();

        if($teamUser){
            $teamId = $teamUser->tm_id;
        }

        $townSales  = $this->getAllocatedTerritories($user);
        $invLine = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$user->getKey())
            ->join('chemist AS c','il.chemist_id','c.chemist_id')
            ->join('product AS p','il.product_id','=','p.product_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->whereBetween('il.invoice_date',[date("Y-m-01 00:00:00"),date("Y-m-31 23:59:59")])
            ->whereIn('il.product_id',$products->pluck('product_id')->all()),'c.sub_twn_id',$townSales->pluck('sub_twn_id')->all())
            ->select([
                'c.chemist_id',
                'il.identity',
                'il.product_id',
                InvoiceLine::salesAmountColumn('bdgt_value'),
                InvoiceLine::salesQtyColumn('gross_qty'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ])
            ->groupBy('c.chemist_id','il.product_id')
            ->whereNull('il.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('st.deleted_at')
            ->get();


        $retLine = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation( DB::table('return_lines AS rl'),$user->getKey(),true)
            ->join('chemist AS c','rl.chemist_id','c.chemist_id')
            ->join('product AS p','rl.product_id','=','p.product_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
            })
            ->whereBetween('rl.invoice_date',[date("Y-m-01 00:00:00"),date("Y-m-31 23:59:59")])
            ->whereIn('rl.product_id',$products->pluck('product_id')->all()),'c.sub_twn_id',$townSales->pluck('sub_twn_id')->all(),true)
            ->select([
                'c.chemist_id',
                'rl.identity',
                'rl.product_id',
                InvoiceLine::salesAmountColumn('bdgt_value',true),
                InvoiceLine::salesQtyColumn('gross_qty',true),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
            ])
            ->groupBy('c.chemist_id','rl.product_id')
            ->whereNull('rl.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('st.deleted_at')
            ->get();

        $chemists->transform(function($chemist)use($invLine,$retLine){
            return [
                'invoice_lines'=>$invLine->where('identity',$chemist->chemist_code),
                'return_lines'=>$retLine->where('identity',$chemist->chemist_code),
                'sub_twn_id'=>$chemist->sub_twn_id
            ];
        });

        // Transforming results
        $allocatedTowns->transform(function($chem,$key)use($teamProducts,$chemists,$products){

            $chemistsForSubTowns = $chemists->where('sub_twn_id',$chem->sub_twn_id);
            $modifiedTeamProducts =  $teamProducts->toArray();

            foreach($chemistsForSubTowns as $chemistsForSubTown){
                foreach($chemistsForSubTown['invoice_lines'] AS $invLine){
                    foreach ($modifiedTeamProducts as $key=>  $prd) {
                        if($prd['product_id']==$invLine->product_id){
                            $modifiedTeamProducts[$key]['qty'] = $modifiedTeamProducts[$key]['qty']+($invLine->gross_qty);
                            $modifiedTeamProducts[$key]['amount'] = round($modifiedTeamProducts[$key]['amount']+($invLine->bdgt_value),2);
                        }
                    }
                }

                foreach($chemistsForSubTown['return_lines'] AS $retLine){
                    foreach ($modifiedTeamProducts as $key=>  $prd) {
                        if($prd['product_id']==$retLine->product_id){
                            $modifiedTeamProducts[$key]['qty'] = $modifiedTeamProducts[$key]['qty']-($retLine->gross_qty);
                            $modifiedTeamProducts[$key]['amount'] = round($modifiedTeamProducts[$key]['amount']-($retLine->bdgt_value),2);
                        }
                    }
                }
            }

            return [
                "sub_twn_id"=>$chem->sub_twn_id,
                "sub_twn_code"=>$chem->sub_twn_code,
                "sub_twn_name"=>$chem->sub_twn_name,
                "product_details"=>$modifiedTeamProducts,
            ];

        });

        $allocatedTowns = $allocatedTowns->sortBy('sub_twn_name');
        return view('WebView/Medical.trend',['twn_wise_pro'=>$allocatedTowns,'product'=>$teamProducts]);
    }
}
