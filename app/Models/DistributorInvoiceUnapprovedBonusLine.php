<?php
namespace App\Models;

/**
 * # Unapproved Bonus Lines of invoices
 * 
 * @property int $di_id
 * @property int $diubl_qty
 * @property int $bns_id
 * @property int $product_id
 * 
 * @property Bonus $bonus
 * @property DistributorInvoice $distributorInvoice
 * @property Product $product
 * 
 */
class DistributorInvoiceUnapprovedBonusLine extends Base {
    protected $table = 'distributor_invoice_unapproved_bonus_line';

    protected $primaryKey = 'diubl_id';

    protected $fillable = [
        'di_id',
        'diubl_qty',
        'bns_id',
        'product_id',
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

}