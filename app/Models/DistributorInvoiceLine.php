<?php

namespace App\Models;

/**
 * Distributor Invoice Lines
 * 
 * @property int $di_id
 * @property int $product_id
 * @property int $db_id
 * @property float $dil_unit_price
 * @property int $dil_qty
 * @property float $dil_discount_percent Discount Percentage
 * @property int $dil_batch_edited Batch edited status (1=Edited)
 * 
 * @property DistributorBatch $batch
 * @property Product $product
 * @property DistributorInvoice $invoice
 */
class DistributorInvoiceLine extends Base {
    protected $table = 'distributor_invoice_line';

    protected $primaryKey = 'dil_id';

    protected $fillable = [
        'di_id',
        'product_id',
        'db_id',
        'dil_unit_price',
        'dil_qty',
        'dil_discount_percent',
        'unit_price_no_tax',
        'dil_batch_edited'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function batch(){
        return $this->belongsTo(DistributorBatch::class,'db_id','db_id');
    }

    public function invoice(){
        return $this->belongsTo(DistributorInvoice::class,'di_id','di_id');
    }
}