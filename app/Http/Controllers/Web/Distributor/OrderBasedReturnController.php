<?php

namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\BonusDistributor;
use App\Models\BonusExclude;
use App\Models\DistributorInvoice;
use App\Models\DistributorInvoiceLine;
use App\Models\DistributorReturn;
use Illuminate\Http\Request;
use App\Models\DistributorInvoiceBonusLine;
use App\Models\DistributorReturnBonusItem;
use App\Models\DistributorReturnItem;
use App\Models\DistributorStock;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderBasedReturnController extends Controller {
    public function getInvoiceInfo(Request $request){
        $invoiceNumber = $request->input('invNumber');

        if(!$invoiceNumber||trim($invoiceNumber)==""){
            throw new WebAPIException("Please provide an invoice number to search.");
        }

        /** @var DistributorInvoice $invoice */
        $invoice = DistributorInvoice::where('di_number',$invoiceNumber)->with([
            'lines',
            'bonusLines',
            'lines.product',
            'lines.batch',
            'bonusLines.product',
            'bonusLines.bonus',
            'bonusLines.batch',
        ])->first();

        if(!$invoice){
            throw new WebAPIException("Can not find an invoice for the given invoice number.");
        }

       $return  = DistributorReturn::where('di_id',$invoice->getKey())->first();
       
       if($return){
           throw new WebAPIException("This invoice is already returned.");
       }

       if($invoice->di_approve_requested_at&&!$invoice->di_approved_at)
            throw new WebAPIException("Can not return this invoice. Bonus approval is pending.");

       $lines = $invoice->lines->map(function(DistributorInvoiceLine $distributorInvoiceLine){
            return [
                'product'=>[
                    "label"=>$distributorInvoiceLine->product?$distributorInvoiceLine->product->product_name:"DELETED",
                    'value'=>$distributorInvoiceLine->product_id
                ],
                'batch'=>[
                    "label"=>$distributorInvoiceLine->batch?$distributorInvoiceLine->batch->db_code:"DELETED",
                    "value"=>$distributorInvoiceLine->db_id,
                ],
                'discount'=>$distributorInvoiceLine->dil_discount_percent?:0,
                'orgQty'=>$distributorInvoiceLine->dil_qty,
                'qty'=>0,
                'id'=>$distributorInvoiceLine->getKey(),
                'salable'=>true,
                'reason'=>null
            ];
       });

       return response()->json([
           'lines'=>$lines,
           'bonusLines'=>[],
           'returnNumber'=>DistributorReturn::generateNumber($invoice->dis_id,$invoice->dsr_id)
       ]);
    }

    public function getBonus(Request $request){
        $invoiceNumber = $request->input('invNumber');
        $lines = collect($request->input('lines',[]));


        /** @var DistributorInvoice $invoice */
        $invoice = DistributorInvoice::where('di_number',$invoiceNumber)
            ->with([
                'lines',
                'bonusLines',
                'lines.product',
                'lines.batch',
                'bonusLines.product',
                'bonusLines.bonus',
                'bonusLines.batch',
            ])
            ->first();

        if(!$invoice){
            throw new WebAPIException("Can not find an invoice for the given invoice number.");
        }

        $edited = false;

        // Replacing old values with new ones
        $invoice->lines->transform(function(DistributorInvoiceLine $invoiceLine,$key) use ($lines,&$edited) {
            $line = $lines->where('id',$invoiceLine->getKey())->first();

            if($line){
                $qty = isset($line['qty'])&&$line['qty']?$line['qty']:0;

                if($qty!= $invoiceLine->dil_qty)
                    $edited = true;

                $invoiceLine->dil_qty = isset($line['qty'])&&$line['qty']?$line['qty']:0;
            }

            return $invoiceLine;
        });
        
        $bonuses = $this->generateBonus($invoice, $edited);

        return response()->json([
            'bonusLines'=>$bonuses
        ]);

    }


    /**
     * Generating the bonus for a given sales order
     *
     * @param DistributorInvoice $invoice
     * @return Collection
     */
    private function generateBonus(DistributorInvoice $invoice, $edited = false){

        $lines = $invoice->lines;
        $bonusLines = $invoice->bonusLines;
        $disId = $invoice->dis_id;

        $distributorBonuses = BonusDistributor::where('dis_id',$disId)->get();

        $bonuses = Bonus::whereDate('bns_start_date','<=',$invoice->created_at->format('Y-m-d'))
            ->whereDate('bns_end_date','>=',$invoice->created_at->format('Y-m-d'))
            ->whereIn('bns_id',$invoice->bonusLines->pluck('bns_id')->all())
            ->where(function($query) use($distributorBonuses) {
                $query->orWhereIn('bns_id',$distributorBonuses->pluck('bns_id')->all());
                $query->orWhere('bns_all',1);
            })
            ->with(['freeProducts','freeProducts.product','products','ratios','excludes'])
            ->get();

        $bonuses->transform(function(Bonus $bonus) use ( $lines) {
            $productIds = $bonus->products->pluck('product_id')->all();

            $qty = $lines->whereIn('product_id',$productIds)->sum('dil_qty');

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

        $bonuses = $bonuses->filter(function($row){return !!$row;})->values();

        $bonuses->transform(function(&$item) use ($bonusLines,$edited) {

            $qty = 0;
            foreach ($item['products'] as $key => $product) {
                /** @var DistributorInvoiceBonusLine[] */
                $bonusEffectedLines = $bonusLines->where('bns_id',$item['id'])->where('product_id',$product['value']);
                $qty+= $bonusEffectedLines->sum('dibl_qty');
                if(!$bonusEffectedLines->count()){
                    unset($item['products'][$key]);
                } else {

                    $item['products'][$key]['batchWise'] = [];

                    foreach ($bonusEffectedLines as $bnsKey => $bonusEffectedLine) {
                        $item['products'][$key]['batchWise'][$bonusEffectedLine->db_id] = [
                            'label'=> $bonusEffectedLine->batch->db_code,
                            'value'=> $bonusEffectedLine->db_id,
                            'issuedQty'=> $bonusEffectedLine->dibl_qty,
                            'qty'=> 0
                        ];
                    }
                }

            }

            if(!$edited)
                $item['qty'] = $qty; 

            return $item;
        });


        return $bonuses;
    }

    public function save(Request $request){

        $validation = Validator::make($request->all(),[
            'invNumber'=>'required',
            'lines'=>'required|array',
            'lines.*.id'=>'required|numeric',
            'lines.*.product'=>'required|array',
            'lines.*.batch'=>'required|array',
            'lines.*.qty'=>'required|numeric',
            'bonusLines.*.id'=>'required|required',
            'bonusLines.*.products'=>'required|array',
            'bonusLines.*.products.*.value'=>'required|numeric',
            'bonusLines.*.products.*.batchWise'=>'required|array',
            'bonusLines.*.products.*.batchWise.*.value'=>'required|numeric',
            'bonusLines.*.products.*.batchWise.*.qty'=>'required|numeric',
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please fill all inputs.");
        }

        $invoiceNumber = $request->input('invNumber');
        $lines = $request->input('lines');
        $bonusLines = $request->input('bonusLines');


        $invoice = DistributorInvoice::where('di_number',$invoiceNumber)->first();

        if(!$invoice){
            throw new WebAPIException("Can not find an invoice for the given invoice number.");
        }

        $return  = DistributorReturn::where('di_id',$invoice->getKey())->first();
        
        if($return){
            throw new WebAPIException("This invoice is already returned.");
        }

        try {

            DB::beginTransaction();

            $return = DistributorReturn::create([
                'discount'=>0,
                'dist_return_number'=>DistributorReturn::generateNumber($invoice->dis_id,$invoice->dsr_id),
                'dis_id'=>$invoice->dis_id,
                'dsr_id'=>$invoice->dsr_id,
                'dc_id'=>$invoice->dc_id,
                'return_date'=>date('Y-m-d'),
                'di_id'=>$invoice->getKey(),
            ]);
            
            foreach ($lines as $key => $line) {
                if($line['qty']){

                    if(!$line['reason'])
                        throw new WebAPIException("Please select a reason to return");

                    $price = Product::getPriceForDistributor($line['product']['value'],$line['batch']['value']);
                    $nonVatPrice = Product::getNotVatPriceForDistributor($line['product']['value'],$line['batch']['value']);

                    $returnLine = DistributorReturnItem::create([
                        'rsn_id'=>$line['reason']?$line['reason']['value']:null,
                        'dis_return_id'=>$return->getKey(),
                        'product_id'=>$line['product']['value'],
                        'dri_qty'=>$line['qty'],
                        'dri_price'=>$price,
                        'dri_bns_qty'=>0,
                        'unit_price_no_tax'=>$nonVatPrice,
                        'dri_is_salable'=>$line['salable']?1:0,
                        'db_id'=>$line['batch']['value'],
                        'dri_dis_percent'=>$line['discount']
                    ]);

                    if($line['salable'])
                        DistributorStock::create([
                            'dis_id'=>$invoice->dis_id,
                            'product_id'=>$line['product']['value'],
                            'db_id'=>$line['batch']['value'],
                            // +
                            'ds_credit_qty'=>$line['qty'],
                            // -
                            'ds_debit_qty'=>0,
                            'ds_ref_id'=>$returnLine->getKey(),
                            'ds_ref_type'=>7
                        ]);

                }

            }

            foreach ($bonusLines as $key => $bonusLine) {
                $requiredQty = 0;
                $qty = 0;
                foreach ($bonusLine['products'] as $key => $product) {
                    foreach ($product['batchWise'] as $key => $batch) {
                        if($batch['qty']){
                            $qty += $batch['qty'];
                            $returnBonusLine = DistributorReturnBonusItem::create([
                                'dis_return_id'=>$return->getKey(),
                                'product_id'=>$product['value'],
                                'drbi_qty'=>$batch['qty'],
                                'bns_id'=>$bonusLine['id'],
                                'db_id'=>$line['batch']['value'],
                            ]);
            
                            DistributorStock::create([
                                'dis_id'=>$invoice->dis_id,
                                'product_id'=>$product['value'],
                                'db_id'=>$line['batch']['value'],
                                // +
                                'ds_credit_qty'=>$batch['qty'],
                                // -
                                'ds_debit_qty'=>0,
                                'ds_ref_id'=>$returnBonusLine->getKey(),
                                'ds_ref_type'=>8
                            ]);
                        }
                    }
                }

                if($requiredQty>$qty)
                    throw new WebAPIException("Please enter the required bonus qty.");
            }

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            throw new WebAPIException("Server error appeared. Please contact your system vendor.");
        }

        return response()->json([
            'success'=>true,
            'message'=>"You have successfully placed your order based return."
        ]);
    }
}