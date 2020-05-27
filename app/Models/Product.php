<?php

namespace App\Models;

use App\Ext\SalesPriceList;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use App\Traits\Team;
use App\Models\Team as TeamModel;
use App\Exceptions\WebAPIException;

/**
 * Product Model
 *
 * @property int $product_id Auto Increment ID
 * @property string $product_code
 * @property string $product_short_name
 * @property string $product_name
 * @property int $brand_id
 * @property int $principal_id
 * @property int $product_family_id
 * @property int $divi_id Division Id
 * @property string $pack_size
 *
 * @property Brand $brand
 * @property ProductFamily $product_family
 * @property Principal $principal
 * @property Division $division
 * @property ProductLatestPriceInformation $latestPriceInfo
 * @property SalesPriceList $budget_price
 * @property SalesmanValidPart $SaleManValidPart
 */
class Product extends Base
{
    use Team;
    protected $table = 'product';

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_code',
        'product_short_name',
        'product_name',
        'brand_id',
        'principal_id',
        'product_family_id',
        'divi_id',
        'pack_size',
        'tax_code_id',
        'tax_code',
        'tax_code_desc'
    ];

    protected $codeName = 'product_code';

    public function brand(){
        return $this->belongsTo(Brand::class,'brand_id','brand_id');
    }
    public function product_family(){
        return $this->belongsTo(ProductFamily::class,'product_family_id','product_family_id');
    }
    public function principal(){
        return $this->belongsTo(Principal::class,'principal_id','principal_id');
    }
    public function division(){
        return $this->belongsTo(Division::class,'divi_id','divi_id');
    }

    public function latestPriceInfo(){
        return $this->hasOne(ProductLatestPriceInformation::class,'product_id','product_id');
    }

    public function budget_price(){
        return $this->belongsTo(SalesPriceList::class,'product_code','catalog_no')->where('price_list_no','=','BDGT');
    }

    public function SaleManValidPart(){
        return $this->hasMany(SalesmanValidPart::class,'product_id','product_id');
    }

    public function tax_code(){
        return $this->belongsTo(TaxCode::class,'tax_code_id','tax_code_id');
    }
    /**
     * Appending budget price to a product
     *
     * @param Collection $products
     * @param string $key
     * @return Collection
     */
    public static function appendBudgetPrice($products , $outputKey="budget_price",$productCodeKey="product_code"){

        $productCodes = $products->pluck($productCodeKey)->all();

        $budgetPrices = DB::table("ext_sales_price_list_uiv AS p1")
            ->select(["p1.*"])->join("ext_sales_price_list_uiv AS p2",function(JoinClause $join)use($productCodes){
                $join->on("p1.catalog_no","p2.catalog_no");
                $join->on("p1.last_updated_on","<","p2.last_updated_on");
                $join->where("p2.price_list_no","BDGT");
                $join->whereIn("p2.catalog_no",$productCodes);
            },null,null,"left")
            ->whereNull("p2.catalog_no")
            ->where("p1.price_list_no","BDGT")
            ->whereIn("p1.catalog_no",$productCodes)
            ->get();

        $salesPrices = DB::table("ext_sales_price_list_uiv AS p1")
            ->select(["p1.*"])->join("ext_sales_price_list_uiv AS p2",function(JoinClause $join)use($productCodes){
                $join->on("p1.catalog_no","p2.catalog_no");
                $join->on("p1.last_updated_on","<","p2.last_updated_on");
                $join->where("p2.price_list_no","PG01");
                $join->whereIn("p2.catalog_no",$productCodes);
            },null,null,"left")
            ->whereNull("p2.catalog_no")
            ->where("p1.price_list_no","PG01")
            ->whereIn("p1.catalog_no",$productCodes)
            ->get();

        $products->transform(function($product)use($budgetPrices,$salesPrices,$productCodeKey,$outputKey){

            $productCode = is_array($product)?$product[$productCodeKey]:$product->{$productCodeKey};

            $price = $budgetPrices->where("catalog_no",$productCode)->first();
            if(!$price) $price = $salesPrices->where("catalog_no",$productCode)->first();

            if(\is_array($product))
                $product[$outputKey] = $price? $price->sales_price:0;
            else
                $product->{$outputKey} = $price? $price->sales_price:0;

            return $product;
        });

        return $products;
    }

    /**
     * Returning products by user
     *
     * @param User $user
     * @param array $with
     * @param callback $func
     * @return Collection
     */
    public static function getByUser($user,$with=[],$func=null){
        $productIds = [];

        // Sending all team products to field manager
        if($user->getRoll()==config("shl.field_manager_type")){
            $team = TeamModel::with('teamProducts')->where('fm_id',$user->getKey())->latest()->first();

            if(!$team)
                throw new WebAPIException("Field manager does not in any team",28);

            foreach($team->teamProducts as $teamProduct){
                $productIds[] = $teamProduct->product_id;
            }
        } else {
            $teamUser = TeamUser::with('team','teamUserProducts','teamUserProducts.teamUser','teamUserProducts.teamUser.user','teamUserProducts.teamProduct','teamUserProducts.teamProduct')->where('u_id',$user->getKey())->latest()->first();

            if(!$teamUser||!$teamUser->team)
                throw new WebAPIException("Medical representaive does not in any team",21);

            $teamUserProducts = $teamUser->teamUserProducts;

            $teamUserProducts = $teamUserProducts->filter(function($teamUserProduct){ return $teamUserProduct->teamUser && $teamUserProduct->teamUser->user; })->values();

            if(!$teamUserProducts->isEmpty()){
                // Sending allocated products if allocated

                foreach ($teamUserProducts as $teamUserProduct) {

                    if($teamUserProduct&&$teamUserProduct->teamProduct){
                        $productIds[] = $teamUserProduct->teamProduct->product_id;
                    }
                }
            } else {
                $teamUsers = TeamUser::with(['teamUserProducts'])->where('tm_id',$teamUser->tm_id)->get();

                $allocated = false;

                foreach($teamUsers as $otherTeamUser){
                    if(!$otherTeamUser->teamUserProducts->isEmpty()) $allocated=true;
                }

                if($allocated){
                    // If products allocated to one user in the team
                    $productIds=[];
                } else {
                    // If not allocated to any member
                    $team = TeamModel::with('teamProducts')->where('tm_id',$teamUser->tm_id)->latest()->first();

                    if(!$team)
                        throw new WebAPIException("Field manager does not in any team",28);

                    foreach($team->teamProducts as $teamProduct){
                        $productIds[] = $teamProduct->product_id;
                    }
                }
            }

        }

        $query = self::with($with);
        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $query->whereIn('product_id',$productIds);
        }

        // Team allocation
        $teams = UserTeam::where('u_id',$user->getKey())->get();
        if($teams->count()){
            $products = TeamProduct::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
            $query->whereIn('product_id',$products->pluck('product_id')->all());
        }

        if($func){
            $query->where($func);
        }

        return $query->get();
    }

    public static function getByUserForSales($user,$with=[],$func=null){
        $products = self::getByUser($user,$with,$func);

        $productIds = [];

        $teamUser = TeamUser::where('u_id',$user->getKey())->latest()->first();

        if($teamUser){
            $salesAllocations = SalesAllocationMain::where('tm_id',$teamUser->tm_id)->get();

            $memberPercentages = TeamMemberPercentage::where('u_id',$teamUser->u_id)->where('mp_percent','>',0)->whereIn('sam_id',$salesAllocations->pluck('sam_id'))->get();

            $salesAllocationProducts = SalesAllocation::where('sa_ref_type',3)->whereIn('sam_id',$memberPercentages->pluck('sam_id'))->get();

            $invoiceAllocationProducts = InvoiceAllocation::with(['invoiceLine'])->where('tm_id',$teamUser->tm_id)->get();

            $productIds = array_merge($productIds,$invoiceAllocationProducts->pluck('invoiceLine.product_id')->all());
            $productIds = array_merge($productIds,$salesAllocationProducts->pluck('sa_ref_id')->all());

            $salesAllocatedProducts = Product::whereIn('product_id',$productIds)->with($with)->get();

            $products = $products->concat($salesAllocatedProducts)->unique('product_id');

        }

        return $products;
    }

    public static function getPriceForDistributor($productId,$batchId=null, $disId = null){
        if($batchId){
            /** @var DistributorBatch $batch */
            $batch = DistributorBatch::where('product_id',$productId)
                ->where('db_id',$batchId)
                ->orderBy('db_expire')
                ->first();
        } else {

            /** @var DistributorBatch $batch */
            // $batch = DistributorBatch::where('product_id',$productId)->orderBy('db_expire')->first();

            $batch = DB::table('distributor_stock AS ds')
                ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                ->select(['db.db_tax_price',DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock')])
                ->where('db.product_id',$productId)
                ->where(function($query) use ($disId){
                    if(!empty($disId))
                        $query->where('ds.dis_id',$disId);
                })
                ->whereDate('db.db_expire','>=',date('Y-m-d'))
                ->groupBy('db.db_id')
                ->orderBy('db.db_expire')
                ->having('stock','>','0')
                ->first();
        }

        if(!$batch)
            return 0;

        return $batch->db_tax_price;
    }

    public static function getNotVatPriceForDistributor($productId,$batchId=null){
        if($batchId){
            /** @var DistributorBatch $batch */
            $batch = DistributorBatch::where('product_id',$productId)
                ->where('db_id',$batchId)
                ->orderBy('db_expire')
                ->first();
        } else {
            /** @var DistributorBatch $batch */
            // $batch = DistributorBatch::where('product_id',$productId)->orderBy('db_expire')->first();

            $batch = DB::table('distributor_stock AS ds')
                ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                ->select(['db.db_price',DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock')])
                ->where('db.product_id',$productId)
                ->whereDate('db.db_expire','>=',date('Y-m-d'))
                ->groupBy('db.db_id')
                ->orderBy('db.db_expire')
                ->having('stock','>','0')
                ->first();
        }

        if(!$batch)
            return 0;

        return $batch->db_price;
    }

}
