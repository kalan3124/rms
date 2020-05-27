<?php

namespace App\Models;

/**
 * Lines of GRN
 * 
 * @property int $grnl_id Auto increment ID
 * @property int $grn_id GRN Id
 * @property int $db_id Distributor Batch Id
 * @property int $grnl_qty GRN Line Qty
 * @property int $product_id
 * 
 * Integration Data
 * @property string $grnl_uom Not usefull (Using only when printing)
 * @property int $grnl_line_no Not usefull (Using only when printing)
 * @property float $grnl_org_qty Original value received from ERP
 * @property float $grnl_price Price sent from the ERP
 * @property float $grnl_tax_price This price is using for VAT customers
 * 
 * @property string $grnl_loc_no Using to avoid duplications and GRN print
 * @property string $grnl_lot_batch_no  Using to avoid duplications
 * 
 * @property GoodReceivedNote $goodReceivedNote
 * @property Product $product
 * @property DistributorBatch $distributorBatch
 */
class GoodReceivedNoteLine extends Base {
    protected $table = 'good_received_note_line';

    protected $primaryKey = 'grnl_id';

    protected $fillable = [
        'grn_id',
        'product_id',
        'db_id',
        'grnl_qty',
        'grnl_uom',
        'grnl_loc_no',
        'grnl_line_no',
        'grnl_org_qty',
        'grnl_price',
        'grnl_lot_batch_no'
    ];

    public function goodReceivedNote(){
        return $this->belongsTo(GoodReceivedNote::class,'grn_id','grn_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function distributorBatch(){
        return $this->belongsTo(DistributorBatch::class,'db_id','db_id');
    }
}