<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\DistributorStock;
use App\Models\Product;
use App\Models\ProductLatestPriceInformation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderConfirmController extends Controller {
    public function load(Request $request){
        $number = $request->input('number');

        if(empty(trim($number)))
            throw new WebAPIException("Please fill the PO number field.");

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = PurchaseOrder::with(['lines','lines.product'])->where('po_number','LIKE',"%$number%")->oldest()->first();

        if(!$purchaseOrder){
            throw new WebAPIException("Can not find a purchase order for the given number.");
        }

        if($purchaseOrder->integrated_at){
            throw new WebAPIException("This purchase order is already integrated.");
        }

        $disId = $purchaseOrder->dis_id;

        $lines = $purchaseOrder->lines->map(function(PurchaseOrderLine $line,$key) use($disId) {

            /** @var ProductLatestPriceInformation $latestPrice */
            $latestPrice = ProductLatestPriceInformation::where('product_id',$line->product_id)->latest()->first();

            $pending = DB::table('purchase_order AS po')
                ->join('purchase_order_lines AS pol','pol.po_id','po.po_id')
                ->leftJoin('good_received_note AS grn','grn.po_id','po.po_id')
                ->whereNull('grn.grn_id')
                ->whereNull('grn.deleted_at')
                ->whereNull('po.deleted_at')
                ->whereNull('pol.deleted_at')
                ->where('pol.product_id',$line->product_id)
                ->where('po.dis_id',$disId)
                ->sum('pol.pol_qty');

            return [
                'product'=> $line->product?[
                    'label'=>$line->product->product_name,
                    'value'=>$line->product->getKey()
                ]:[
                    'label'=>'DELETED',
                    'value'=>0
                ],
                'price'=>$latestPrice?($latestPrice->lpi_bdgt_sales>0?$latestPrice->lpi_bdgt_sales:$latestPrice->lpi_pg01_sales):0.00,
                'packSize'=>empty($line->product->pack_size)?"N/A": $line->product->pack_size,
                'stockInHand'=> DistributorStock::checkStock($disId,$line->product_id),
                'stockPending'=> $pending,
                'qty'=>$line->pol_qty,
                'id'=>$key
            ];
        });

        return response()->json([
            'success'=>true,
            'lines'=>$lines
        ]);
    }


    public function save(Request $request){

        $number = $request->input('number');
        $lines = $request->input('lines');

        if(empty(trim($number)))
            throw new WebAPIException("Please fill the PO number field.");

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = PurchaseOrder::with(['lines','lines.product'])->where('po_number','LIKE',"%$number%")->oldest()->first();

        if(!$purchaseOrder){
            throw new WebAPIException("Can not find a purchase order for the given number.");
        }

        if($purchaseOrder->integrated_at){
            throw new WebAPIException("This purchase order is already integrated.");
        }


        $insertedIds = [];

        try {
            DB::beginTransaction();

            foreach ($lines as $line){
                $existLine = $purchaseOrder->lines->where('product_id',$line['product']['value'])->first();

                if($existLine){
                    $existLine->pol_qty = $line['qty'];
                    $existLine->save();
                } else {
                    $existLine = PurchaseOrderLine::create([
                        'po_id'=>$purchaseOrder->getKey(),
                        'product_id'=>$line['product']['value'],
                        'pol_qty'=>$line['qty'],
                        'pol_org_qty'=>$line['qty'],
                        'pol_price'=>$line['price'],
                        'pol_amount'=>$line['price']*$line['qty'],
                    ]);
                }

                $insertedIds[] = $existLine->getKey();
            }

            PurchaseOrderLine::where('po_id',$purchaseOrder->getKey())->whereNotIn('pol_id',$insertedIds)->delete();

            $integrated = $purchaseOrder->sendToIFS();

            if(!$integrated)
                throw new WebAPIException("Purchased order not integrated to IFS. Please contact your system vendor.");

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
        
            throw $e;
        }

        return response()->json([
            'success'=>true,
            'message'=>"Successfully integrated your purchase order"
        ]);
    }

    public function getDetails(Request $request){
        $purchaseOrderNumber = $request->input('poNumber');

        $purchaseOrder = PurchaseOrder::where('po_number','like',"$purchaseOrderNumber%")->oldest()->first();
        
        $productId = $request->input('product.value');

        if(!$productId||!$purchaseOrder){
            return response()->json([
                'price'=>0.00,
                'pack_size' => 'N/A',
                'code' => 'N/A',
                'stockInHand'=> 0,
                'stockPending'=> 0
            ]);
        }

        $distributorId = $purchaseOrder->dis_id;

        $product = Product::find($productId);

        $latestPrice = ProductLatestPriceInformation::with('product')->where('product_id',$productId)->latest()->first();

        $pending = DB::table('purchase_order AS po')
            ->join('purchase_order_lines AS pol','pol.po_id','po.po_id')
            ->leftJoin('good_received_note AS grn','grn.po_id','po.po_id')
            ->whereNull('grn.grn_id')
            ->whereNull('grn.deleted_at')
            ->whereNull('po.deleted_at')
            ->whereNull('pol.deleted_at')
            ->where('pol.product_id',$productId)
            ->where('po.dis_id',$distributorId)
            ->sum('pol.pol_qty');

        return response()->json([
            'price'=>$latestPrice?($latestPrice->lpi_bdgt_sales>0?$latestPrice->lpi_bdgt_sales:$latestPrice->lpi_pg01_sales):0.00,
            'pack_size' =>  empty($product->pack_size)?"N/A": $product->pack_size,
            'code' => $product->product_code,
            'stockInHand'=> DistributorStock::checkStock($distributorId,$productId),
            'stockPending'=> $pending
        ]);
    }
}