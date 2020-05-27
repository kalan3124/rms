<?php

namespace App\Http\Controllers\API\Medical\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Traits\Territory as TerritoryTrait;
use App\Traits\Team as TeamTrait;
use App\Models\ProductiveVisit;
use Illuminate\Support\Facades\Auth;
use App\Models\UnproductiveVisit;
use App\Models\Chemist;
use App\Ext\Get\SalesPart;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    use TerritoryTrait,TeamTrait;

    public function promotion()
    {
        return [
            'result' => true,
            'promotion' => [
                [
                    'promo_id' => 1,
                    'promo_name' => 'New Doctor Promotion December',
                ],
                [
                    'promo_id' => 2,
                    'promo_name' => 'AAA Product 2 in 1 promotion',
                ],
                [
                    'promo_id' => 3,
                    'promo_name' => 'BBB Product 12 in 3 Promotion',
                ],
            ],
        ];
    }
    /**
     * Returning the monthly sales summery
     *
     * @return void
     */
    public function getMonthlySalesSummery(){
        $user = Auth::user();

        $productiveCount = $this->makeQueryWithUserMonthFilters(ProductiveVisit::class,$user)->count();

        $unproductiveCount = $this->makeQueryWithUserMonthFilters(UnproductiveVisit::class,$user)->count();

        $plannedVisits = $this->makeQueryWithUserMonthFilters(ProductiveVisit::class,$user)->where('is_shedule',0)->count();

        $unPlannedVisits = $this->makeQueryWithUserMonthFilters(ProductiveVisit::class,$user)->where('is_shedule',1)->count();


        $allocatedTerritories = $this->getAllocatedTerritories($user);

        $chemists = Chemist::whereIn('sub_twn_id',$allocatedTerritories->pluck('sub_twn_id')->all())->get();



        return [
            'result'=>true,
            'monthly_details'=>[
                'visitCounts'=>[
                    'productive'=>$productiveCount,
                    'unproductive'=>$unproductiveCount,
                    'planned'=>$plannedVisits,
                    'unplanned'=>$unPlannedVisits
                ]
            ]
        ];
    }
    /**
     * Returning a query with user and current month filters
     *
     * @param \App\Models\Base $model
     * @param \App\Models\User $user
     * @return \Illuminate\Database\Query\Builder
     */
    protected function makeQueryWithUserMonthFilters($model,$user){
        $query = $model::where('u_id',$user->getKey())->whereMonth('created_at',date('m'))->whereYear('created_at',date('Y'));

        return $query;
    }

    public function oracleTest()
    {
        $data = SalesPart::limit(20)->get();

        return $data;
    }

    public function limitedChemistSale(){
        $user = Auth::user(); 

        $itineraryAreas = $this->getAllocatedTerritories($user);
        $itinerarySubTownIds = $itineraryAreas->pluck('sub_twn_id')->all();
        $itinerarySubTownCodes = $itineraryAreas->pluck('sub_twn_code')->all();

        // Getting assigned customers for user
        // $assignedCustomers = UserCustomer::where('u_id',$user->getKey())->whereNull('doc_id')->with('chemist')->get();
        // $chemistIds = $assignedCustomers->pluck('chemist_id');

        // Team products for sales rep
        $teamProducts = $this->getProductsByUser($user);
        
        $teamProducts->transform(function($product){
            return [
                'product_id'=>$product->getKey(),
                'product_code'=>$product->product_code,
                'product_name'=>$product->product_name,
                'budget_value'=>0,
                'qty'=>0
            ];
        });

        $products = Product::appendBudgetPrice($teamProducts,"budget_price","product_code");

        $chemists = Chemist::whereIn('sub_twn_id',$itinerarySubTownIds)->limit(100)->get();

        $invLine = DB::table('invoice_line AS il')
                ->join('invoice AS i','i.inv_head_id','=','il.inv_head_id')
                ->whereBetween('il.invoice_date',[date("Y-m-01 00:00:00"),date("Y-m-31 23:59:59")])
                ->whereIn('il.product_id',$products->pluck('product_id')->all())
                ->whereIn('il.city',$itinerarySubTownCodes)
                ->select([
                    'i.chemist_id',
                    'il.identity',
                    'il.product_id',
                    DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS gross_qty')
                    ])
                ->groupBy('i.chemist_id','il.product_id')
                ->get();

        $retLine = DB::table('return_lines AS rl')
            ->join('invoice AS i','i.inv_head_id','=','rl.inv_head_id')
            ->whereBetween('rl.invoice_date',[date("Y-m-01 00:00:00"),date("Y-m-31 23:59:59")])
            ->whereIn('rl.product_id',$products->pluck('product_id')->all())
            ->whereIn('rl.city',$itinerarySubTownCodes)
            ->select([
                'i.chemist_id',
                'rl.identity',
                'rl.product_id',
                DB::raw('IFNULL(SUM(rl.invoiced_qty),0) AS gross_qty')
                ])
            ->groupBy('i.chemist_id','rl.product_id')
            ->get();

        $chemists->transform(function($chemist)use($invLine,$retLine,$teamProducts,$products){ 

            $invLine = $invLine->where('identity',$chemist->chemist_code);
            $retLine = $retLine->where('identity',$chemist->chemist_code);

            $modifiedTeamProducts =  $teamProducts->toArray();

            foreach($invLine AS $invLine){
                foreach ($modifiedTeamProducts as $key=>  $prd) {
                    if($prd['product_id']==$invLine->product_id){
                        $pro = $products->where('product_id',$invLine->product_id)->first();
                        $modifiedTeamProducts[$key]['qty'] = $modifiedTeamProducts[$key]['qty']+($invLine->gross_qty);
                        $modifiedTeamProducts[$key]['budget_value'] = $modifiedTeamProducts[$key]['budget_value']+($invLine->gross_qty * $pro['budget_price']);
                    }
                }
            }

            foreach($retLine AS $retLine){
                foreach ($modifiedTeamProducts as $key=>  $prd) {
                    if($prd['product_id']==$retLine->product_id){
                        $pro = $products->where('product_id',$retLine->product_id)->first();
                        $modifiedTeamProducts[$key]['qty'] = $modifiedTeamProducts[$key]['qty']-($retLine->gross_qty);
                        $modifiedTeamProducts[$key]['budget_value'] = $modifiedTeamProducts[$key]['budget_value']-($retLine->gross_qty * $pro['budget_price']);
                    }
                }
            }

            return [
                'sub_twn_id'=>$chemist->sub_twn_id,
                'chemist_id'=>$chemist->chemist_id,
                'chemist_code'=>$chemist->chemist_code,
                'invoices'=>$modifiedTeamProducts
            ];
            
        });

        return [
            "result"=>true,
            "chemist_sales_details"=>$chemists
        ];
    }
}
