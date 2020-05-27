<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\BonusDistributor;
use App\Models\BonusExclude;
use App\Models\BonusFreeProduct;
use App\Models\BonusProduct;
use App\Models\DistributorBatch;
use App\Models\DistributorCustomer;
use App\Models\DistributorInvoice;
use App\Models\DistributorInvoiceBonusLine;
use App\Models\DistributorInvoiceLine;
use App\Models\DistributorInvoiceUnapprovedBonusLine;
use App\Models\DistributorStock;
use App\Models\Product;
use App\Models\ProductLatestPriceInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DirectInvoiceController extends Controller {
    public function getNextInvoiceNumber(Request $request){
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
            'number'=>DistributorInvoice::generateNumber($distributor)
        ]);
    }

    public function loadBatchDetails(Request $request){

        $distributorId = $request->input('distributor.value');
        $productId = $request->input('product.value');
        $invoiceQty = $request->input('qty');

        $hasStock = true;

        $batchDetails = [];

        $stocks =  DB::table('distributor_stock AS ds')
            ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
            ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.db_id','db.db_code','db.db_expire'])
            ->where('ds.dis_id',$distributorId)
            ->where('db.product_id',$productId)
            ->whereDate('db.db_expire','>=',date('Y-m-d'))
            ->groupBy('db.db_id')
            ->orderBy('db.db_expire')
            ->having('stock','>','0')
            ->get();

        if($stocks->count()<=0)
            return response()->json([
                'success'=>true,
                'batchDetails'=> [],
                'hasStock'=>false
            ]);

        $index = 0;
        while($invoiceQty){
            $stock = $stocks->get($index);

            $qty = 0;
            if($stock->stock>=$invoiceQty){
                $qty = $invoiceQty;
            } else {
                $qty = $stock->stock;
            }

            $price = Product::getPriceForDistributor($productId,$stock->db_id);

            $invoiceQty-= $qty;

            $batchDetails[] = [
                'code'=>$stock->db_code,
                'price'=>$price,
                'availableStock'=>$stock->stock,
                'qty'=>$qty,
                'expire'=>date("m/d/y" ,strtotime($stock->db_expire))
            ];

            $index++;

            if($index>$stocks->count()-1&&$invoiceQty){
                $hasStock = false;
                break;
            }

        }


        return response()->json([
            'success'=>true,
            'batchDetails'=> $hasStock? $batchDetails : [],
            'hasStock'=>$hasStock
        ]);
    }

    public function loadLineInfo(Request $request){

        $distributor = $request->input('distributor.value');
        $product = $request->input('product.value');
        $customerId = $request->input('customer.value');

        $stocks =  DB::table('distributor_stock AS ds')
            ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
            ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.db_id AS id','db.db_code AS code','db.db_expire AS expire',DB::raw('0 AS qty')])
            ->where('ds.dis_id',$distributor)
            ->where('db.product_id',$product)
            ->whereDate('db.db_expire','>=',date('Y-m-d'))
            ->groupBy('db.db_id')
            ->orderBy('db.db_expire')
            ->having('stock','>','0')
            ->get();

        return response()->json([
            'price'=>Product::getPriceForDistributor($product, null, $distributor),
            'stock'=>DistributorStock::checkStock($distributor,$product),
            'success'=>true,
            'availableBatches'=>$stocks
        ]);
    }

    public function save(Request $request){
        $distributor = $request->input('distributor.value');
        $salesman = $request->input('salesman.value');
        $customer = $request->input('customer.value');
        $lines = $request->input('lines');
        $bonusLines = $request->input('bonusLines',[]);
        $toApprove = $request->input('toApprove');

        $disnumber = DistributorInvoice::generateNumber($distributor);


        /** @var DistributorCustomer $customer */
        $customer = DistributorCustomer::find($customer);

        $validation = Validator::make($request->all(),[
            'distributor'=>'required|array',
            'distributor.value'=>'required|numeric|exists:users,id',
            'salesman'=>'required|array',
            'salesman.value'=>'required|numeric|exists:users,id',
            'customer'=>'required|array',
            'customer.value'=>'required|numeric|exists:distributor_customer,dc_id',
            'lines'=>'required|array',
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please make sure all fields filled.");
        }

        try{

            DB::beginTransaction();

            $invoice = DistributorInvoice::create([
                'di_amount'=>0,
                'di_discount'=>0,
                'dsr_id'=>$salesman,
                'dis_id'=>$distributor,
                'dc_id'=>$customer->getKey(),
                'di_number'=>DistributorInvoice::generateNumber($distributor),
                'di_is_direct'=>1,
                'di_vat_percentage'=>$customer->dc_is_vat ? config('shl.vat_percentage') : 0,
                'di_approve_requested_at'=>$toApprove?date('Y-m-d H:i:s'):null,
                'di_approve_requested_by'=>$toApprove? Auth::user()->getKey():null
            ]);

            $invoiceTotalQty = 0;
            $invoiceTotalAmount = 0;
            $invoiceTotalDiscount = 0;
            foreach ($lines as $key => $line) {
                if(!isset($line['product'])||!isset($line['product']['value']))
                    throw new WebAPIException("Please select a product.");

                $id = $line['id'];

                $invoiceQty = $line['qty'];
                $invoiceTotalQty += $invoiceQty;


                $stock = DistributorStock::checkStock($distributor,$line['product']['value']);

                $product = Product::find($line['product']['value']);

                if(!$product)
                    throw new WebAPIException("Invalid request");

                $productCode = $product->product_code;

                if($stock<$invoiceQty)
                    throw new WebAPIException("Stock has changed for '$productCode' and not enough stock. Please click 'Search' button to load again the invoicing view");


                $lineAmount = 0;
                $lineDiscount = 0;

                $stocks =  DB::table('distributor_stock AS ds')
                    ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                    ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.db_id'])
                    ->where('ds.dis_id',$distributor)
                    ->where('db.product_id',$product->getKey())
                    ->whereDate('db.db_expire','>=',date('Y-m-d'))
                    ->groupBy('db.db_id')
                    ->orderBy('db.db_expire')
                    ->having('stock','>','0')
                    ->get();

                $batchQtys = collect($line['availableBatches']);

                if($batchQtys->sum('qty')>0&&$batchQtys->sum('qty')!=$invoiceQty)
                    throw new WebAPIException("Batch qty and invoice line qty mismatched for '$productCode'.");

                if($stocks->count()<=0)
                    throw new WebAPIException("Stock has changed during processing time for '$productCode' and not enough stock. Please click 'Search' button to load again the invoicing view");
                $index = 0;

                if($batchQtys->sum('qty')==0){
                    while($invoiceQty){
                        $stock = $stocks->get($index);

                        $qty = 0;
                        if($stock->stock>=$invoiceQty){
                            $qty = $invoiceQty;
                        } else {
                            $qty = $stock->stock;
                        }

                        $price = Product::getPriceForDistributor($product->getKey(),$stock->db_id);
                        $notVatPrice = Product::getNotVatPriceForDistributor($product->getKey(),$stock->db_id);
                        $notVat = $notVatPrice;

                        $invoiceQty-= $qty;
                        $lineAmount += $price*$qty;
                        $lineDiscount += $price*$qty*($line['discount']/100);

                        $invoiceLine = DistributorInvoiceLine::create([
                            'di_id'=>$invoice->getKey(),
                            'product_id'=>$product->product_id,
                            'db_id'=>$stock->db_id,
                            'dil_unit_price'=>$price,
                            'dil_qty'=>$qty,
                            'dil_discount_percent'=>$line['discount'],
                            'unit_price_no_tax' => $notVat
                        ]);

                        DistributorStock::create([
                            'dis_id'=>$distributor,
                            'product_id'=>$product->product_id,
                            'db_id'=>$stock->db_id,
                            'ds_credit_qty'=>0,
                            'ds_debit_qty'=>$qty,
                            'ds_ref_id'=>$invoiceLine->getKey(),
                            'ds_ref_type'=>2,
                        ]);

                        $index++;

                        if($index>$stocks->count()-1&&$invoiceQty){
                            throw new WebAPIException("Stock has changed during processing time for '$productCode' and not enough stock. Please click 'Search' button to load again the invoicing view");
                        }

                    }
                } else {
                    foreach ($batchQtys as $batchQty){
                        $stock = $stocks->where('db_id',$batchQty['id'])->first();

                        $qty = $batchQty['qty'];

                        if($stock->stock<$batchQty['qty'])
                            throw new WebAPIException("Stock has changed during processing time for '$productCode' and not enough stock. Please click 'Search' button to load again the invoicing view");

                        $price = Product::getPriceForDistributor($product->getKey(),$stock->db_id);
                        $notVatPrice = Product::getNotVatPriceForDistributor($product->getKey(),$stock->db_id);
                        $notVat = $notVatPrice;

                        $invoiceQty-= $qty;
                        $lineAmount += $price*$qty;
                        $lineDiscount += $price*$qty*($line['discount']/100);

                        $invoiceLine = DistributorInvoiceLine::create([
                            'di_id'=>$invoice->getKey(),
                            'product_id'=>$product->product_id,
                            'db_id'=>$stock->db_id,
                            'dil_unit_price'=>$price,
                            'dil_qty'=>$qty,
                            'dil_discount_percent'=>$line['discount'],
                            'unit_price_no_tax' => $notVat,
                            'dil_batch_edited'=>1
                        ]);

                        DistributorStock::create([
                            'dis_id'=>$distributor,
                            'product_id'=>$product->product_id,
                            'db_id'=>$stock->db_id,
                            'ds_credit_qty'=>0,
                            'ds_debit_qty'=>$qty,
                            'ds_ref_id'=>$invoiceLine->getKey(),
                            'ds_ref_type'=>2,
                        ]);
                    }
                }

                $invoiceTotalAmount += $lineAmount;
                $invoiceTotalDiscount += $lineDiscount;

            }

            if(!$toApprove){
                foreach ($bonusLines as $key => $bonusLine) {
                    $id = $bonusLine['id'];

                    foreach ($bonusLine['products'] as $key => $line) {

                        $bonusQty = $line['qty']?:0;
                        if($bonusQty>0){
                            $stock = DistributorStock::checkStock($distributor,$line['value']);

                            $product = Product::find($line['value']);

                            if(!$product)
                                throw new WebAPIException("Invalid request");

                            $productCode = $product->product_code;

                            if($stock<$bonusQty)
                                throw new WebAPIException("Bonus product '$productCode' has not enough stock.");


                            $stocks =  DB::table('distributor_stock AS ds')
                                ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                                ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.db_id'])
                                ->where('ds.dis_id',$distributor)
                                ->where('db.product_id',$product->getKey())
                                ->whereDate('db.db_expire','>=',date('Y-m-d'))
                                ->groupBy('db.db_id')
                                ->orderBy('db.db_expire')
                                ->having('stock','>','0')
                                ->get();

                            if($stocks->count()<=0)
                                throw new WebAPIException("Bonus Product '$productCode' has not enough stock.");
                            $index = 0;
                            while($bonusQty){
                                $stock = $stocks->get($index);

                                $qty = 0;
                                if($stock->stock>=$bonusQty){
                                    $qty = $bonusQty;
                                } else {
                                    $qty = $stock->stock;
                                }

                                $bonusQty-= $qty;

                                $bonusLine = DistributorInvoiceBonusLine::create([
                                    'di_id'=>$invoice->getKey(),
                                    'product_id'=>$product->product_id,
                                    'db_id'=>$stock->db_id,
                                    'dibl_unit_price'=>Product::getPriceForDistributor($product->getKey(),$stock->db_id),
                                    'dibl_qty'=>$qty,
                                    'bns_id'=>$id
                                ]);

                                DistributorStock::create([
                                    'dis_id'=>$distributor,
                                    'product_id'=>$product->product_id,
                                    'db_id'=>$stock->db_id,
                                    'ds_credit_qty'=>0,
                                    'ds_debit_qty'=>$qty,
                                    'ds_ref_id'=>$bonusLine->getKey(),
                                    'ds_ref_type'=>6,
                                ]);

                                $index++;

                                if($index>$stocks->count()-1&&$bonusQty){
                                    throw new WebAPIException("Stock has changed during processing time for '$productCode' and not enough stock. Please click 'Search' button to load again the invoicing view");
                                }
                            }

                        }

                    }

                }
            } else {
                foreach ($bonusLines as $key => $bonusLine) {

                    $id = $bonusLine['id'];

                    foreach ($bonusLine['products'] as $key => $line) {

                        $bonusQty = $line['qty']?:0;
                        if($bonusQty>0){
                            $product = Product::find($line['value']);

                            if(!$product)
                                throw new WebAPIException("Invalid request");

                            DistributorInvoiceUnapprovedBonusLine::create([
                                'di_id'=>$invoice->getKey(),
                                'diubl_qty'=>$bonusQty,
                                'product_id'=>$product->getKey(),
                                'bns_id'=>$id
                            ]);

                        }

                    }

                }
            }

            $invoice->di_amount = $invoiceTotalAmount;
            $invoice->di_discount = $invoiceTotalDiscount;
            $invoice->save();

            if(!$invoiceTotalQty)
                throw new WebAPIException("Can not make an empty invoice.");

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();

            throw $e;
        }

        return response()->json([
            'success'=>true,
            'message'=>$disnumber.' You have successfully created the invoice'
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
                        'label'=>$bonusFreeProduct->product->product_name
                    ];
            }

            $qty = floor($qty/$purchaseQty)*$freeQty;

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
