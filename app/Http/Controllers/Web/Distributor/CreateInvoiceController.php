<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\BonusDistributor;
use App\Models\BonusExclude;
use App\Models\DistributorInvoice;
use App\Models\DistributorInvoiceBonusLine;
use App\Models\DistributorInvoiceLine;
use App\Models\DistributorInvoiceUnapprovedBonusLine;
use App\Models\DistributorSalesOrder;
use App\Models\DistributorSalesOrderProduct;
use App\Models\DistributorStock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CreateInvoiceController extends Controller {
    public function load(Request $request){

        $order = $this->getOrder($request->input('soNumber'));

        $bonuses = $this->generateBonus($order);

        $lines = $order->salesOrderProducts;
        $bonusLines = $order->salesOrderBonusProducts;

        $bonuses->transform(function($item) use ($bonusLines) {

            foreach ($item['products'] as $key => $product) {

                $bonusLineQty = $bonusLines->where('bns_id',$item['id'])->where('product_id',$product['value'])->sum('dsobp_qty');

                $item['products'][$key]['qty'] = $bonusLineQty ;

            }
            
            return $item;
        });


        $lines->transform(function( DistributorSalesOrderProduct $line) use($order){
            $stock = DistributorStock::checkStock($order->dis_id,$line->product_id);

            $stocks =  DB::table('distributor_stock AS ds')
                ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.db_id AS id','db.db_code AS code','db.db_expire AS expire',DB::raw('0 AS qty')])
                ->where('ds.dis_id',$order->dis_id)
                ->where('db.product_id',$line->product_id)
                ->whereDate('db.db_expire','>=',date('Y-m-d'))
                ->groupBy('db.db_id')
                ->orderBy('db.db_expire')
                ->having('stock','>','0')
                ->get();

            $stocks = $stocks->mapWithKeys(function ($item) {
                return [$item->id => $item];
            });


            return [
                'product'=>isset($line->product)?[
                    'label'=>$line->product->product_name,
                    'value'=>$line->product->product_id,
                ]:[
                    'label'=>'DELETED',
                    'value'=>0
                ],
                'id'=>$line->getKey(),
                'invoiceQty'=>$line->sales_qty,
                'availableStock'=>$stock,
                'soQty'=>$line->sales_qty,
                'batchDetails'=>$this->generateBatchDetails($order->dis_id,$line->product_id,$line->sales_qty),
                'unitPrice'=>$line->price,
                'discountPercent'=>$line->di_discount?$line->di_discount:0,
                'availableBatches'=>$stocks
            ];
        });

        return response()->json([
            'success'=> true,
            'details'=>$lines,
            'remark'=>$order->remark,
            'bonusDetails'=>$bonuses,
            'discount'=> $order->discount
        ]);
    }

    /**
     * Returniing the order by sales order number
     *
     * @param string $soNumber
     * @return DistributorSalesOrder
     */
    protected function getOrder(string $soNumber){

        $order= DistributorSalesOrder::where('order_no',$soNumber)->with(['salesOrderProducts','salesOrderProducts.product','distributorCustomer','salesOrderBonusProducts'])->latest()->first();

        if(!$order)
            throw new WebAPIException("Can not find a SO for your input");


        if($order->is_invoiced){
            throw new WebAPIException("This sales order has already invoiced.");
        }

        return $order;
    }

    public function save(Request $request){
        $details = $request->input('details');
        $bonusDetails = $request->input('bonusDetails');
        $toApprove = $request->input('toApprove');

        $order = $this->getOrder($request->input('soNumber'));
        $customer = $order->distributorCustomer;

        $lines = $order->salesOrderProducts;

        try{

            DB::beginTransaction();

            /** @var DistributorInvoice $invoice */
            $invoice = DistributorInvoice::create([
                'dist_order_id'=>$order->getKey(),
                'di_amount'=>0,
                'di_discount'=>0,
                'dsr_id'=>$order->u_id,
                'dis_id'=>$order->dis_id,
                'dc_id'=>$order->dc_id,
                'di_number'=>DistributorInvoice::generateNumber($order->dis_id),
                'di_vat_percentage'=>$customer->dc_is_vat ? config('shl.vat_percentage') : 0 ,
                'di_approve_requested_at'=>$toApprove?date('Y-m-d H:i:s'):null,
                'di_approve_requested_by'=>$toApprove? Auth::user()->getKey():null
            ]);

            $invoiceTotalQty = 0;
            $invoiceTotalAmount = 0;
            $invoiceTotalDiscount = 0;
            foreach ($details as $key => $line) {
                $id = $line['id'];

                $invoiceQty = $line['invoiceQty'];
                $invoiceTotalQty += $invoiceQty;

                $salesOrderLine = $lines->where('dist_order_pro_id',$id)->first();

                if(!$salesOrderLine)
                    throw new WebAPIException("Invalid request. Can not process the request.");

                $stock = DistributorStock::checkStock($order->dis_id,$salesOrderLine->product_id);
                
                if(!$salesOrderLine->product)
                    throw new WebAPIException("Can not invoice a deleted product.");

                $productCode = $salesOrderLine->product->product_name;
                $product = $salesOrderLine->product;
                if($stock<$invoiceQty)
                    throw new WebAPIException("Stock has changed for '$productCode' and not enough stock. Please click 'Search' button to load again the invoicing view");


                $lineAmount = 0;

                $stocks =  DB::table('distributor_stock AS ds')
                    ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                    ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.db_id'])
                    ->where('ds.dis_id',$order->dis_id)
                    ->where('db.product_id',$salesOrderLine->product_id)
                    ->whereDate('db.db_expire','>=',date('Y-m-d'))
                    ->groupBy('db.db_id')
                    ->orderBy('db.db_expire')
                    ->having('stock','>','0')
                    ->get();

                $batchQtys = collect($line['availableBatches']);

                if($batchQtys->sum('qty')>0&&$batchQtys->sum('qty')!=$invoiceQty)
                    throw new WebAPIException("Batch qty and invoice line qty mismatched for '$productCode'.");

                if($stocks->count()<0)
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

                        $invoiceQty-= $qty;
                        $price = Product::getPriceForDistributor($salesOrderLine->product_id,$stock->db_id);
                        $lineAmount += $price*$qty;

                        $notVatPrice = Product::getNotVatPriceForDistributor($salesOrderLine->product_id,$stock->db_id);
                        $notVat = $notVatPrice; 

                        $invoiceLine = DistributorInvoiceLine::create([
                            'di_id'=>$invoice->getKey(),
                            'product_id'=>$salesOrderLine->product_id,
                            'db_id'=>$stock->db_id,
                            'dil_unit_price'=> $price,
                            'dil_qty'=>$qty,
                            'dil_discount_percent'=>$line['discountPercent'],
                            'unit_price_no_tax' => $notVat
                        ]);

                        DistributorStock::create([
                            'dis_id'=>$order->dis_id,
                            'product_id'=>$salesOrderLine->product_id,
                            'db_id'=>$stock->db_id,
                            'ds_credit_qty'=>0,
                            'ds_debit_qty'=>$qty,
                            'ds_ref_id'=>$invoiceLine->getKey(),
                            'ds_ref_type'=>2,
                        ]);

                        $invoiceTotalDiscount += $price*$qty*$line['discountPercent']/100;

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

                        $invoiceLine = DistributorInvoiceLine::create([
                            'di_id'=>$invoice->getKey(),
                            'product_id'=>$product->product_id,
                            'db_id'=>$stock->db_id,
                            'dil_unit_price'=>$price,
                            'dil_qty'=>$qty,
                            'dil_discount_percent'=>$line['discountPercent'],
                            'unit_price_no_tax' => $notVat,
                            'dil_batch_edited'=>1
                        ]);

                        DistributorStock::create([
                            'dis_id'=>$order->dis_id,
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

            }

            if(!$toApprove){
                
                foreach ($bonusDetails as $key => $bonusLine) {

                    $id = $bonusLine['id'];

                    foreach ($bonusLine['products'] as $key => $line) {
                        
                        $bonusQty = $line['qty']?:0;
                        if($bonusQty>0){
                            $stock = DistributorStock::checkStock($order->dis_id,$line['value']);

                            $product = Product::find($line['value']);

                            if(!$product)
                                throw new WebAPIException("Invalid request");

                            $productCode = $product->product_code;

                            if($stock<$bonusQty)
                                throw new WebAPIException("Bonus product '$productCode' has not enough stock.");


                            $stocks =  DB::table('distributor_stock AS ds')
                                ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                                ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.db_id'])
                                ->where('ds.dis_id',$order->dis_id)
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
                                    'dis_id'=>$order->dis_id,
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
                foreach ($bonusDetails as $key => $bonusLine) {

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

            $order->is_invoiced = 1;
            $order->save();

            if(!$invoiceTotalQty)
                throw new WebAPIException("Can not make an empty invoice.");
                
            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();

            throw $e;
        }

        return response()->json([
            'success'=>true,
            'message'=>"Successfully invoiced the sales order."
        ]);

    }

    
    /**
     * Generating the bonus for a given sales order
     *
     * @param DistributorSalesOrder $salesOrder
     * @return Collection
     */
    private function generateBonus(DistributorSalesOrder $salesOrder){

        $lines = $salesOrder->salesOrderProducts;
        $disId = $salesOrder->dis_id;

        $distributorBonuses = BonusDistributor::where('dis_id',$disId)->get();

        $bonuses = Bonus::whereDate('bns_start_date','<=',date('Y-m-d'))
            ->whereDate('bns_end_date','>=',date('Y-m-d'))
            ->where(function($query) use($distributorBonuses) {
                $query->orWhereIn('bns_id',$distributorBonuses->pluck('bns_id')->all());
                $query->orWhere('bns_all',1);
            })
            ->with(['freeProducts','freeProducts.product','products','ratios','excludes'])
            ->get();

        $bonuses->transform(function(Bonus $bonus) use ( $lines) {
            $productIds = $bonus->products->pluck('product_id')->all();

            $qty = $lines->whereIn('product_id',$productIds)->sum('sales_qty');

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

        return $bonuses->values();
    }

    public function loadBonus(Request $request){
        $soNumber = $request->input('soNumber');
        $details = $request->input('details');

        $order = $this->getOrder($soNumber);

        $lines = $order->salesOrderProducts;

        foreach ($details as $key => $detail) {
            $line = $lines->where('dist_order_pro_id',$key)->first();

            $line->sales_qty = $detail['invoiceQty'];

        }

        $bonuses = $this->generateBonus($order);

        return response()->json([
            'success'=>true,
            'bonusDetails'=>$bonuses
        ]);
    }

    protected function generateBatchDetails($distributorId,$productId,$invoiceQty){

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
            return [];

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
                'expire'=> date("m/d/y" ,strtotime($stock->db_expire))
            ];

            $index++;

            if($index>$stocks->count()-1&&$invoiceQty){
                $hasStock = false;
                break;
            }

        }

        if(!$hasStock)
            return [];

        return $batchDetails;

    }


    public function loadBatchDetails(Request $request){

        $soNumber = $request->input('soNumber');

        $salesOrder = DistributorSalesOrder::where('order_no','LIKE',$soNumber)->oldest()->first();

        if(!$salesOrder)
            throw new WebAPIException("Can not find a sales order.");

        $distributorId = $salesOrder->dis_id;

        $productId = $request->input('product.value');
        $invoiceQty = $request->input('qty');


        return response()->json([
            'success'=>true,
            'batchDetails'=> $this->generateBatchDetails($distributorId,$productId,$invoiceQty)
        ]);
    }

}
