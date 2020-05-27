<?php
namespace App\Models;

/**
 * Bonus Lines of invoices
 * 
 * @property int $di_id
 * @property int $dibl_qty
 * @property int $bns_id
 * @property int $product_id
 * @property int $db_id
 * @property float $dibl_unit_price
 * 
 * @property Bonus $bonus
 * @property DistributorInvoice $distributorInvoice
 * @property Product $product
 * @property DistributorBatch $batch
 * 
 */
class DistributorInvoiceBonusLine extends Base {
    protected $table = 'distributor_invoice_bonus_line';

    protected $primaryKey = 'dibl_id';

    protected $fillable = [
        'di_id',
        'dibl_qty',
        'bns_id',
        'product_id',
        'db_id',
        'dibl_unit_price'
    ];

    public function bonus(){
        return $this->belongsTo(Bonus::class,'bns_id','bns_id');
    }

    public function distributorInvoice(){
        return $this->belongsTo(DistributorInvoice::class,'di_id','di_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function batch(){
        return $this->belongsTo(DistributorBatch::class,'db_id','db_id');
    }
}