<?php

namespace App\Ext\Get;

use App\Models\DistributorBatch;
use App\Models\DistributorStock;
use App\Models\GoodReceivedNote;
use App\Models\GoodReceivedNoteLine;
use App\Models\Product;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use LaravelTreats\Model\Traits\HasCompositePrimaryKey;

class InvoiceLineDelivery extends Get
{
    use HasCompositePrimaryKey;

    // protected $connection = 'mysql2';

    protected $table = 'ifsapp.EXT_INVOICE_LINE_DELIVERY_UIV';
    // protected $table = 'ext_invoice_line_delivery_uiv';

    public $hasCompositePrimary = true;

    public $primaryKey = ['invoice_id','item_id'];

    public $hasPrimary = true;


    public function afterCreate($inst, $data)
    {
        $this->createDuplicate($data);
    }

    protected function createDuplicate($data){
        $purchaseOrder = PurchaseOrder::where(DB::raw('TRIM(po_number)'),trim($data['customer_po_no']))->latest()->first();

        if($purchaseOrder){
            $grnNo = $data['series_id'].'/'.$data['invoice_no'];

            $grn = GoodReceivedNote::where('grn_no',$grnNo)
                ->latest()
                ->first();

            if(!$grn){
                $grn = GoodReceivedNote::create([
                    'grn_no'=>$grnNo,
                    'dis_id'=>$purchaseOrder->dis_id,
                    'dsr_id'=>$purchaseOrder->sr_id,
                    'po_id'=>$purchaseOrder->getKey(),
                    'grn_amount'=>0,
                    'grn_date'=>$data['invoice_date'],
                    'grn_org_amount'=>0,
                ]);
            }

            if(substr($data['catalog_no'], 0, 2) == 'B-'){
                $product = Product::where('product_code',ltrim($data['catalog_no'], 'B-'))
                ->latest()
                ->first();
            } else {
                $product = Product::where('product_code',$data['catalog_no'])
                ->latest()
                ->first();
            }

            if($product){
                $batch = DistributorBatch::where('db_code',$data['waiv_dev_rej_no'])
                    ->where('product_id',$product->getKey())
                    ->latest()
                    ->first();

                if($batch){
                    if($data['sale_unit_price']>0){
                        $batch->db_price = $data['sale_unit_price'];
                    }

                    if($data['unit_price_incl_tax']>0){
                        $batch->db_tax_price = $data['unit_price_incl_tax'];
                    }

                    $batch->db_expire = $data['expiration_date'];
                    $batch->save();
                } else {

                    $batch = DistributorBatch::create([
                        'db_code'=>$data['waiv_dev_rej_no'],
                        'db_price'=>$data['sale_unit_price'],
                        'db_tax_price'=>$data['unit_price_incl_tax'],
                        'product_id'=>$product?$product->getKey():null,
                        'db_expire'=>$data['expiration_date']
                    ]);
                }

                /** @var GoodReceivedNoteLine $grnlExist */
                $grnlExist = GoodReceivedNoteLine::where('grn_id',$grn->getKey())
                    ->where('product_id',$batch->product_id)
                    ->where('db_id',$batch->getKey())
                    ->where(function($query) use ($data) {
                        $query->orWhere('grnl_lot_batch_no','LIKE',$data['lot_batch_no']);
                        $query->orWhereNull('grnl_lot_batch_no');
                    })
                    ->where(function($query) use ($data) {
                        $query->orWhere('grnl_loc_no','LIKE',$data['location_no']);
                        $query->orWhereNull('grnl_loc_no');
                    })
                    ->first();

                if(!$grnlExist){
                    /** @var GoodReceivedNoteLine $grnl */
                    $grnl = GoodReceivedNoteLine::create([
                        'grn_id'=>$grn->getKey(),
                        'product_id'=>$batch->product_id,
                        'db_id'=>$batch->getKey(),
                        'grnl_qty'=>$data['qty_shipped'],
                        'grnl_uom'=>$data['sale_um'],
                        'grnl_loc_no'=>$data['location_no'],
                        'grnl_lot_batch_no'=>$data['lot_batch_no'],
                        'grnl_line_no'=>$data['line_no'],
                        'grnl_org_qty'=>$data['qty_shipped'],
                        'grnl_price'=>$data['sale_unit_price'],
                        'grnl_tax_price'=>$data['unit_price_incl_tax']
                    ]);

                    $grn->grn_amount = $grn->grn_amount + $data['qty_shipped']*$batch->db_price;
                    $grn->grn_org_amount = $grn->grn_org_amount +  $data['qty_shipped']*$batch->db_price;
                    $grn->save();
                } else {
                    $grnlExist->update([
                        'grnl_uom'=>$data['sale_um'],
                        'grnl_loc_no'=>$data['location_no'],
                        'grnl_lot_batch_no'=>$data['lot_batch_no'],
                    ]);
                }
            }
        }
    }

    public function afterUpdate($inst, $data)
    {
        $this->createDuplicate($data);
    }
}
