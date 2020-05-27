<?php

namespace App\Models;

/**
 * Purchase order lines
 * 
 * @property int $pol_id Auto Increment Id
 * @property int $po_id Purchase Order Id
 * @property int $product_id Product Id
 * @property int $pol_qty Purchase order linr qty
 * @property int $pol_org_qty Qty before confirm
 * @property float $pol_price Unit price of the product when creating the PO
 * @property float $pol_amount qty*price
 * 
 * @property Product $product
 * @property PurchaseOrder $purchaseOrder
 */
class PurchaseOrderLine extends Base {
    protected $table = 'purchase_order_lines';

    protected $primaryKey = 'pol_id';

    protected $fillable = [
        'po_id',
        'product_id',
        'pol_qty',
        'pol_org_qty',
        'pol_price',
        'pol_amount'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function purchaseOrder(){
        return $this->belongsTo(PurchaseOrder::class,'po_id','po_id');
    }
}