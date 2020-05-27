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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller {

    protected function generateNumber($distributor){
        if(!$distributor){
            return '';
        }

        return PurchaseOrder::generateNumber($distributor);
    }

    public function getNumber(Request $request){
        $distributor = $request->input('distributor.value');

        return response()->json([
            'number'=>$this->generateNumber($distributor)
        ]);
    }

    public function getDetails(Request $request){

        $productId = $request->input('product.value');
        $distributorId = $request->input('distributor.value');

        if(!$productId){
            return response()->json([
                'price'=>0.00,
                'pack_size' => 'N/A',
                'code' => 'N/A',
                'stockInHand'=> 0,
                'stockPending'=> 0
            ]);
        }

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

    public function save(Request $request){

        $distributor = $request->input('distributor.value');
        $lines = $request->input('lines');
        $dsr = $request->input('dsr');
        $site = $request->input('site');

        $ponumber= $this->generateNumber($distributor);

        if(!$distributor||!$dsr){
            throw new WebAPIException("Please select a distributor and DSR to create the purchase order.");
        }

        if(!$site){
            throw new WebAPIException("Please provide a site to create a purchase order");
        }

        $user = Auth::user();

        $integrated = false;

        try {
            DB::beginTransaction();


            $purchaseOrder = PurchaseOrder::create([
                'dis_id'=>$distributor,
                'created_u_id'=>$user->getKey(),
                'po_number'=>$this->generateNumber($distributor),
                'po_amount'=>0,
                'sr_id'=>$dsr['value'],
                'site_id'=>$site['value']
            ]);

            $amount = 0;

            foreach ($lines as $key => $line) {
                $number = $key+1;
                if(!isset($line['product']))
                    throw new WebAPIException("Please select a product in $number line.");

                if ($line['qty']<1)
                    throw new WebAPIException("Please select a qty in $number line.");


                $amount += (float)( $line['price'] * $line['qty']);

                PurchaseOrderLine::create([
                    'po_id'=>$purchaseOrder->getKey(),
                    'product_id'=>$line['product']['value'],
                    'pol_qty'=>$line['qty'],
                    'pol_org_qty'=>$line['qty'],
                    'pol_price'=>$line['price'],
                    'pol_amount'=>$line['price']*$line['qty']
                ]);
            }

            $purchaseOrder->po_amount = $amount;

            $purchaseOrder->save();

            // $integrated = $purchaseOrder->sendToIFS();

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success'=>true,
            'message'=>$integrated.$ponumber." You have successfully placed your purchase order.:Your purchase order has successfully."
        ]);
    }
}
