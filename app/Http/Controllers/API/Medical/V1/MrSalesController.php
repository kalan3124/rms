<?php
namespace App\Http\Controllers\API\Medical\V1;

use App\Exceptions\MediAPIException;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Chemist;

use App\Models\Area;

use App\Traits\Territory as TerritoryTrait;
use App\Models\ProductiveVisit;
use App\Models\UnproductiveVisit;
use App\Traits\Team as TeamTrait;
use App\Models\UserTarget;
use Illuminate\Http\Request;
use Validator;
use App\Models\Product;
use App\Models\InvoiceLine;
use App\Models\UserProductTarget;

class MrSalesController extends Controller{

    use TerritoryTrait,TeamTrait;

    public function mrAssignedArea(){
        $user= Auth::user();

        $itineraryTowns = $this->getAllocatedTerritories($user);

        $itineraryAreaIds = [];
        // If itinerary towns not set return all town ids
        // Other wise take only town ids from above results
        if($itineraryTowns->isEmpty()){
            $itineraryAreaIds = Area::all()->pluck('ar_id');
        }
        else {
            $itineraryAreaIds = $itineraryTowns->pluck('ar_id');
        }

        //Get Area Details
        $area = Area::whereIn('ar_id',$itineraryAreaIds)->get();
        $area->transform(function ($area) {
            return [
                'ar_id'=>$area->ar_id,
                'ar_code'=>$area->ar_code,
                'ar_name'=>$area->ar_name
            ];
        });
        return [
            "result"=>true,
            "areas"=>$area
        ];
    }

    public function mrAssignedProducts(){
        $user= Auth::user();

        $products = $this->getProductsByUser($user);

        $products->transform(function($product){
            return [
                'product_id'=>$product->getKey(),
                'product_code'=>$product->product_code,
                'product_name'=>$product->product_name
            ];
        });

        return [
            "result"=>true,
            "products"=>$products
        ];
    }

    public function salesDetails(Request $request){
        $user= Auth::user();

        $itineraryAreas = $this->getAllocatedTerritories($user);

        $itinerarySubTownIds = $itineraryAreas->pluck('sub_twn_id')->all();
        $itinerarySubTownCodes = $itineraryAreas->pluck('sub_twn_code')->all();

        $products = $this->getProductsByUser($user);

        $userTarget = UserTarget::where('u_id',$user->getKey())->with('userProductTargets')->latest()->first();

        $products->transform(function($product) use ($userTarget){

            $target = $userTarget&&$userTarget->userProductTargets?$userTarget->userProductTargets->where('product_id',$product->getKey())->first():null;

            return [
                'product_id'=>$product->getKey(),
                'product_code'=>$product->product_code,
                'product_name'=>$product->product_name,
                'inv_amount'=>0,
                'inv_qty'=>0,
                'target_qty'=>$target?$target->upt_qty:0,
                'target_amount'=>$target?$target->upt_value:0
            ];
        });

        // Getting assigned customers for user

        $chemists = Chemist::with(['sub_town','sub_town.town'])->whereIn('sub_twn_id',$itinerarySubTownIds)->get();

        $itineraryAreas = $itineraryAreas->groupBy('ar_id')->values();

        $invLineQuery  = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$user->getKey())
            ->join('chemist AS c','il.chemist_id','c.chemist_id')
            ->join('product AS p','il.product_id','=','p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->whereBetween('il.invoice_date',[date("Y-m-01 00:00:00"),date("Y-m-t 23:59:59")])
            ->whereIn('il.product_id',$products->pluck('product_id')->all()) ,'il.city',$itinerarySubTownCodes);

        $retLineQuery = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'),$user->getKey(),true)
            ->join('chemist AS c','rl.chemist_id','c.chemist_id')
            ->join('product AS p','rl.product_id','=','p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->whereBetween('rl.invoice_date',[date("Y-m-01 00:00:00"),date("Y-m-t 23:59:59")])
            ->whereIn('rl.product_id',$products->pluck('product_id')->all()),'rl.city',$itinerarySubTownCodes,true);

        $timestamp = $request->input('timestamp');

        if($timestamp){
            $invCount = $invLineQuery->whereBetween('il.invoice_date',[date("Y-m-d H:i:s",$timestamp/1000),date("Y-m-t 23:59:59")])->count();
            $retCount = $retLineQuery->whereBetween('rl.invoice_date',[date("Y-m-d H:i:s",$timestamp/1000),date("Y-m-t 23:59:59")])->count();

            if($invCount||$retCount){
                throw new MediAPIException("You have no latest data.",38);
            }
        }


        $invLine = $invLineQuery
            ->select([
                'c.chemist_id',
                'il.identity',
                'il.product_id',
                InvoiceLine::salesAmountColumn('bdgt_value'),
                InvoiceLine::salesQtyColumn('gross_qty'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ])
            ->groupBy('c.chemist_id','il.product_id')
            ->get();

        $retLine = $retLineQuery
            ->select([
                'c.chemist_id',
                'rl.identity',
                'rl.product_id',
                InvoiceLine::salesAmountColumn('bdgt_value',true),
                InvoiceLine::salesQtyColumn('gross_qty',true),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ])
            ->groupBy('c.chemist_id','rl.product_id')
            ->get();

        $chemists->transform(function($chemist)use($invLine,$retLine){
            return [
                'invoices'=>$invLine->where('identity',$chemist->chemist_code),
                'invoice_returns'=>$retLine->where('identity',$chemist->chemist_code),
                'sub_twn_id'=>$chemist->sub_twn_id,
                'ar_id'=>($chemist->sub_town->town)?$chemist->sub_town->town->ar_id:''
            ];
        });
        // Transforming results
        $itineraryAreas->transform(function($chem,$key)use($products,$chemists){

            $clonedProducts =  clone $products;

            $chemistsForAreas = $chemists->where('ar_id',$chem->first()->ar_id);

            // return $chemistsForAreas;

            foreach($chemistsForAreas as $chemistsForArea){
                foreach($chemistsForArea['invoices'] as $invoice){
                    $clonedProducts->transform(function($product) use ($invoice){
                        if($product['product_id']==$invoice->product_id){
                            $product['inv_amount'] = round($product['inv_amount']+($invoice->bdgt_value),2);
                            $product['inv_qty'] = $product['inv_qty']+($invoice->gross_qty);
                        }
                        return $product;
                    });
                }

                foreach($chemistsForArea['invoice_returns'] as $return){
                    $clonedProducts->transform(function($product) use ($return){
                        if($product['product_id']==$return->product_id){
                            $product['inv_amount'] = round($product['inv_amount']-($return->bdgt_value),2);
                            $product['inv_qty'] = $product['inv_qty']-($return->gross_qty);
                        }
                        return $product;
                    });
                }
            }

            $clonedProducts = collect($clonedProducts);
            $clonedInvProducts = $clonedProducts->where('inv_qty','!=',0);
            $clonedTrgProducts = $clonedProducts->where('target_qty','!=',0);

            $clonedProducts = $clonedInvProducts->merge($clonedTrgProducts);

            return [
                "ar_id"=>$chem->first()->ar_id,
                "ar_code"=>$chem->first()->ar_code,
                "ar_name"=>$chem->first()->ar_name,
                "product_details"=>$clonedProducts->values(),
            ];
        });

        return [
            'result'=>true,
            'areas'=>$itineraryAreas,
        ];
    }
    /**
     * Returning the monthly sales summery
     *
     * @return void
     */
    public function getMonthlySalesSummery(Request $request){
        $user = Auth::user();


        $validation = Validator::make($request->all(),[
            'date'=>'required|date'
        ]);

        $date = date("Y-m-d");

        if(!$validation->fails()) {
            $date = $request->input("date");
        }

        $fromDate = date("Y-m-01",strtotime($date));
        $toDate = date("Y-m-d",strtotime($date));

        $productiveCount = $this->makeQueryWithUserMonthFilters(ProductiveVisit::class,$user,$fromDate,$toDate)->count();

        $unproductiveCount = $this->makeQueryWithUserMonthFilters(UnproductiveVisit::class,$user,$fromDate,$toDate)->count();

        $plannedVisits = $this->makeQueryWithUserMonthFilters(ProductiveVisit::class,$user,$fromDate,$toDate)->where('is_shedule',0)->count();

        $unPlannedVisits = $this->makeQueryWithUserMonthFilters(ProductiveVisit::class,$user,$fromDate,$toDate)->where('is_shedule',1)->count();


        $allocatedTerritories = $this->getAllocatedTerritories($user);
        $allocatedSubTownIds = $allocatedTerritories->pluck('sub_twn_id')->all();
        $itinerarySubTownCodes = $allocatedTerritories->pluck('sub_twn_code')->all();

        // Getting assigned customers for user
        // $assignedCustomers = UserCustomer::where('u_id',$user->getKey())->whereNull('doc_id')->with('chemist')->get();
        // $chemistIds = $assignedCustomers->pluck('chemist_id');

        $chemists = Chemist::whereIn('sub_twn_id',$allocatedSubTownIds)->get();

        $products = $this->getProductsByUser($user);

        $products->transform(function($product){
            return [
                'product_id'=>$product->getKey(),
                'product_code'=>$product->product_code,
                'product_name'=>$product->product_name,
            ];
        });

        $invoiceCount = 0;
        $invoiceValue = 0;

        $invLine = InvoiceLine::whereWithSalesAllocation( InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$user->getKey())
            ->join('chemist AS c','il.chemist_id','c.chemist_id')
            ->join('product AS p','il.product_id','=','p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->whereBetween('il.invoice_date',[date("Y-m-01 00:00:00",strtotime($date)),date("Y-m-31 23:59:59",strtotime($date))])
            ->whereIn('il.product_id',$products->pluck('product_id')->all()),'il.city',$itinerarySubTownCodes)
            ->select([
                'c.chemist_id',
                'il.identity',
                'il.product_id',
                InvoiceLine::salesAmountColumn('bdgt_value'),
                InvoiceLine::salesQtyColumn('gross_qty'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price'),
                'il.invoice_no'
            ])
            ->groupBy('c.chemist_id','il.product_id')
            ->get();

        $clone = collect($invLine);

        foreach($invLine AS $invLine){
            $product = $products->where('product_id',$invLine->product_id)->first();
            if($product){
                $invoiceValue+=$invLine->bdgt_value;
            }
        }

        $retLine = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'),$user->getKey(),true)
            ->join('chemist AS c','rl.chemist_id','c.chemist_id')
            ->join('product AS p','rl.product_id','=','p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->whereBetween('rl.invoice_date',[date("Y-m-01 00:00:00",strtotime($date)),date("Y-m-31 23:59:59",strtotime($date))])
            ->whereIn('rl.product_id',$products->pluck('product_id')->all()),'rl.city',$itinerarySubTownCodes,true)
            ->select([
                'c.chemist_id',
                'rl.identity',
                'rl.product_id',
                InvoiceLine::salesQtyColumn('gross_qty',true),
                InvoiceLine::salesAmountColumn('bdgt_value',true),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
            ])
            ->groupBy('c.chemist_id','rl.product_id')
            ->get();

        foreach($retLine AS $retLine){
            $product = $products->where('product_id',$retLine->product_id)->first();
            if($product){
                $invoiceValue-=$retLine->bdgt_value;
            }
        }

        $uniqueInv = $clone->unique('invoice_no');

        $uniqueInv->values()->all();
        $invoiceCount = $uniqueInv->count();

        $target = UserTarget::where('ut_year',date('Y'))->where('ut_month',date('m'))->where('u_id',$user->getKey())->latest()->first();

        if($target)
            $monthlyTarget = UserProductTarget::where('ut_id',$target->getKey())->sum('upt_value');
        else
            $monthlyTarget=0.00;

        return [
            'result'=>true,
            'monthly_details'=>[
                'visitCounts'=>[
                    'productive'=>$productiveCount,
                    'unproductive'=>$unproductiveCount,
                    'planned'=>$plannedVisits,
                    'unplanned'=>$unPlannedVisits
                ],
                'salesOrderCount'=>$invoiceCount,
                'invoiceCount'=>$invoiceCount,
                'salesOrderValue'=>round($invoiceValue,2),
                'invoiceValue'=>round($invoiceValue,2),
                'salesTarget'=>$monthlyTarget
            ]
        ];
    }
    /**
     * Returning a query with user and current month filters
     *
     * @param \App\Base $model
     * @param \App\User $user
     * @return \Illuminate\Database\Query\Builder
     */
    protected function makeQueryWithUserMonthFilters($model,$user,$fromDate,$toDate){
        $query = $model::where('u_id',$user->getKey())
            ->whereDate('created_at','>=', $fromDate)
            ->whereDate('created_at','<=', $toDate);

        return $query;
    }

    /**
     * Returning the monthly sales summery
     *
     * @return void
     */
    public function getChemistWiseSale(Request $request){
        $user = Auth::user();

        $itineraryAreas = $this->getAllocatedTerritories($user);
        $itinerarySubTownIds = $itineraryAreas->pluck('sub_twn_id')->all();
        $itinerarySubTownCodes = $itineraryAreas->pluck('sub_twn_code')->all();

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

        $chemists = Chemist::whereIn('sub_twn_id',$itinerarySubTownIds)->get();


        $invLineQuery  = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$user->getKey())
            ->join('chemist AS c','il.chemist_id','c.chemist_id')
            ->join('product AS p','il.product_id','=','p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->whereBetween('il.invoice_date',[date("Y-m-01 00:00:00"),date("Y-m-t 23:59:59")])
            ->whereIn('il.product_id',$products->pluck('product_id')->all()) ,'il.city',$itinerarySubTownCodes);

        $retLineQuery = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'),$user->getKey(),true)
            ->join('chemist AS c','rl.chemist_id','c.chemist_id')
            ->join('product AS p','rl.product_id','=','p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->whereBetween('rl.invoice_date',[date("Y-m-01 00:00:00"),date("Y-m-t 23:59:59")])
            ->whereIn('rl.product_id',$products->pluck('product_id')->all()),'rl.city',$itinerarySubTownCodes,true);

        $timestamp = $request->input('timestamp');

        if($timestamp){
            $invCount = $invLineQuery->whereBetween('il.invoice_date',[date("Y-m-d H:i:s",$timestamp/1000),date("Y-m-t 23:59:59")])->count();
            $retCount = $retLineQuery->whereBetween('rl.invoice_date',[date("Y-m-d H:i:s",$timestamp/1000),date("Y-m-t 23:59:59")])->count();

            if($invCount||$retCount){
                throw new MediAPIException("You have no latest data.",38);
            }
        }

        $invLine = $invLineQuery
                ->select([
                    'c.chemist_id',
                    'il.identity',
                    'il.product_id',
                    InvoiceLine::salesQtyColumn('gross_qty'),
                    InvoiceLine::salesAmountColumn('bdgt_value'),
                    DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                    ])
                ->groupBy('c.chemist_id','il.product_id')
                ->get();

        $retLine = $retLineQuery
            ->select([
                'c.chemist_id',
                'rl.identity',
                'rl.product_id',
                InvoiceLine::salesQtyColumn('gross_qty',true),
                InvoiceLine::salesAmountColumn('bdgt_value',true),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ])
            ->groupBy('c.chemist_id','rl.product_id')
            ->get();

        $chemists->transform(function($chemist)use($invLine,$retLine,$teamProducts,$products){

            $invLine = $invLine->where('identity',$chemist->chemist_code);
            $retLine = $retLine->where('identity',$chemist->chemist_code);

            $modifiedTeamProducts =  $teamProducts->toArray();

            foreach($invLine AS $invLine){
                foreach ($modifiedTeamProducts as $key=>  $prd) {
                    if($prd['product_id']==$invLine->product_id){
                        $modifiedTeamProducts[$key]['qty'] = $modifiedTeamProducts[$key]['qty']+($invLine->gross_qty);
                        $modifiedTeamProducts[$key]['budget_value'] = $modifiedTeamProducts[$key]['budget_value']+($invLine->bdgt_value);
                    }
                }
            }

            foreach($retLine AS $retLine){
                foreach ($modifiedTeamProducts as $key=>  $prd) {
                    if($prd['product_id']==$retLine->product_id){
                        $modifiedTeamProducts[$key]['qty'] = $modifiedTeamProducts[$key]['qty']-($retLine->gross_qty);
                        $modifiedTeamProducts[$key]['budget_value'] = $modifiedTeamProducts[$key]['budget_value']-($retLine->bdgt_value);
                    }
                }
            }

            $modifiedTeamProducts = collect($modifiedTeamProducts);
            $modifiedTeamProducts = $modifiedTeamProducts->where('qty','!=',0);

            return [
                'sub_twn_id'=>$chemist->sub_twn_id,
                'chemist_id'=>$chemist->chemist_id,
                'chemist_code'=>$chemist->chemist_code,
                'invoices'=>$modifiedTeamProducts->values()
            ];

        });

        return [
            "result"=>true,
            "chemist_sales_details"=>$chemists
        ];

    }

}
