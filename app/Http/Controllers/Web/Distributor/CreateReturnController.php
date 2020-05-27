<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\BonusDistributor;
use App\Models\BonusExclude;
use App\Models\DistributorBatch;
use App\Models\DistributorCustomer;
use App\Models\DistributorReturn;
use App\Models\DistributorReturnBonusItem;
use App\Models\DistributorReturnItem;
use App\Models\DistributorStock;
use App\Models\Product;
use App\Models\ProductLatestPriceInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreateReturnController extends Controller {
    public function getNextReturnNumber(Request $request){
        $distributor = $request->input('distributor.value');
        $salesman = $request->input('salesman.value');

        if(!$distributor||!$salesman){
            return response()->json([
                'success'=>true,
                'number'=>''
            ]);
        }

        return response()->json([
            'success'=>true,
            'number'=>DistributorReturn::generateNumber($distributor,$salesman)
        ]);
    }

    public function loadLineInfo(Request $request){

        $distributor = $request->input('distributor.value');
        $product = $request->input('product.value');
        
        /** @var ProductLatestPriceInformation $price */
        $price = ProductLatestPriceInformation::where('product_id',$product)->first();

        return response()->json([
            'price'=>$price?$price->lpi_bdgt_sales:0.00,
            'stock'=>DistributorStock::checkStock($distributor,$product),
            'success'=>true
        ]);
    }

    public function save(Request $request){
        $distributor = $request->input('distributor.value');
        $salesman = $request->input('salesman.value');
        $customer = $request->input('customer.value');
        $lines = $request->input('lines');
        $bonusLines = $request->input('bonusLines',[]);

        $validation = Validator::make($request->all(),[
            'distributor'=>'required|array',
            'distributor.value'=>'required|numeric|exists:users,id',
            'salesman'=>'required|array',
            'salesman.value'=>'required|numeric|exists:users,id',
            'customer'=>'required|array',
            'customer.value'=>'required|numeric|exists:distributor_customer,dc_id',
            'lines'=>'required|array',
            'lines.*.reason'=>'required|array',
            'lines.*.reason.value'=>'required|exists:reason,rsn_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please make sure all fields filled.");
        }

        try{

            DB::beginTransaction();

            $invoice = DistributorReturn::create([
                'discount'=>0,
                'dsr_id'=>$salesman,
                'dis_id'=>$distributor,
                'dc_id'=>$customer,
                'dist_return_number'=>DistributorReturn::generateNumber($distributor,$salesman),
                'return_date'=>date('Y-m-d')
            ]);

            /** @var DistributorCustomer $customer */
            $customer = DistributorCustomer::find($customer);

            foreach ($lines as $key => $line) {
                if(!isset($line['product'])||!isset($line['product']['value'])||!isset($line['batch'])||!isset($line['batch']['value']))
                    throw new WebAPIException("Please select a product.");

                $id = $line['id'];
    
                $invoiceQty = $line['qty'];

                $product = Product::find($line['product']['value']);

                if(!$product)
                    throw new WebAPIException("Invalid request");

                $productCode = $product->product_code;

                $isSalable = (int) isset($line['salable'])&&$line['salable']?1:0;

                /** @var DistributorBatch $batch */
                $batch = DistributorBatch::find($line['batch']['value']);

                $price = Product::getPriceForDistributor($product->getKey(),$line['batch']['value']);
                $notVatPrice = Product::getNotVatPriceForDistributor($product->getKey(),$line['batch']['value']);
                $notVat = $notVatPrice;


                $returnLine = DistributorReturnItem::create([
                    'dis_return_id'=>$invoice->getKey(),
                    'product_id'=>$product->product_id,
                    'dri_price'=> $price ,
                    'dri_bns_qty'=>0,
                    'dri_qty'=>$invoiceQty,
                    'rsn_id'=>isset($line['reason'])?$line['reason']['value']:null,
                    'dri_is_salable'=>$isSalable,
                    'db_id'=>$line['batch']['value'],
                    'dri_dis_percent'=>$line['discount'],
                    'unit_price_no_tax' => $notVat
                ]);

                if($isSalable){
                    DistributorStock::create([
                        'dis_id'=>$distributor,
                        'product_id'=>$product->product_id,
                        'db_id'=>$line['batch']['value'],
                        'ds_credit_qty'=>$invoiceQty,
                        'ds_debit_qty'=>0,
                        'ds_ref_id'=>$returnLine->getKey(),
                        'ds_ref_type'=> 7
                    ]);
                }

            }


            foreach ($bonusLines as $key => $bonusLine) {

                $id = $bonusLine['id'];

                foreach ($bonusLine['products'] as $key => $line) {
                    
                    $bonusQty = $line['qty']?:0;
                    if($bonusQty>0){
                            if(!isset($line['batch'])||!isset($line['batch']['value']))
                                throw new WebAPIException("Please select a batch.");
                        
                        $bonusLine = DistributorReturnBonusItem::create([
                            'dis_return_id'=>$invoice->getKey(),
                            'product_id'=>$line['value'],
                            'drbi_qty'=>$bonusQty,
                            'bns_id'=>$id,
                            'db_id'=>$line['batch']['value']
                        ]);

                        DistributorStock::create([
                            'dis_id'=>$distributor,
                            'product_id'=>$line['value'],
                            'db_id'=>$line['batch']['value'],
                            'ds_credit_qty'=>$bonusQty,
                            'ds_debit_qty'=>0,
                            'ds_ref_id'=>$bonusLine->getKey(),
                            'ds_ref_type'=> 8
                        ]);
                    }
                    
                }
        
            }
    
            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();

            throw $e;
        }

        return response()->json([
            'success'=>true,
            'message'=>'You have successfully created the invoice'
        ]);
    }

    public function getBonus(Request $request){

        $validation = Validator::make($request->all(),[
            'disId'=>'required|numeric|exists:users,id',
            'lines'=>'required|array',
            'lines.*.qty'=>'required|numeric',
            'lines.*.product'=>'required|array',
            'lines.*.product.value'=>'required|exists:product,product_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request");
        }

        $disId = $request->input('disId');
        $lines = $request->input('lines');

        $lines = collect($lines);

        $distributorBonuses = BonusDistributor::where('dis_id',$disId)->get();

        $bonuses = Bonus::whereDate('bns_start_date','<=',date('Y-m-d'))
            ->whereDate('bns_end_date','>=',date('Y-m-d'))
            ->where(function($query) use($distributorBonuses) {
                $query->orWhereIn('bns_id',$distributorBonuses->pluck('bns_id')->all());
                $query->orWhere('bns_all',1);
            })
            ->with(['freeProducts','freeProducts.product','products','ratios','excludes'])
            ->get();

        $bonuses->transform(function(Bonus $bonus) use ($lines) {
            $productIds = $bonus->products->pluck('product_id')->all();

            $qty = $lines->whereIn('product.value',$productIds)->sum('qty');

            $exists = false;
            $purchaseQty = 0;
            $freeQty = 0;

            foreach ($bonus->ratios as $key => $ratio) {
                if($ratio->bnsr_min<=$qty&&$ratio->bnsr_max>=$qty){
                    $exists = true;
                    $purchaseQty = $ratio->bnsr_purchase;
                    $freeQty = $ratio->bnsr_free;
                }
            }

            if(!$exists)
                return null;

            $freeProducts = [];

            foreach ($bonus->freeProducts as $key => $bonusFreeProduct) {
                if(isset($bonusFreeProduct->product))
                    $freeProducts[$bonusFreeProduct->product->getKey()] = [
                        'qty'=>0,
                        'value'=>$bonusFreeProduct->product->getKey(),
                        'label'=>$bonusFreeProduct->product->product_name,
                        'batch'=>null
                    ];
            }

            $qty = floor(($freeQty/$purchaseQty)*$qty);

            if(!$qty)
                return null;

            return [
                'label'=>$bonus->bns_name,
                'qty'=>$qty,
                'products'=>$freeProducts,
                'id'=>$bonus->getKey(),
                'excludes'=>$bonus->excludes->map(function(BonusExclude $bonusExclude){
                    return $bonusExclude->bnse_bns_id;
                })
            ];
        });

        $bonuses = $bonuses->filter(function($row){return !!$row;});

        return response()->json([
            'success'=>true,
            'lines'=>$bonuses->values()
        ]);
    }
}