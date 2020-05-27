<?php
namespace App\Models;

/**
 * DSR Allocated products
 * 
 * @property int $dsr_id
 * @property int $product_id
 * 
 * @property User $distributor
 * @property Product $product
 */
class DsrProduct extends Base {
    protected $table = 'dsr_product';

    protected $primaryKey = 'dsrp_id';

    protected $fillable = [
        'dsr_id',
        'product_id'
    ];

    public function distributor(){
        return $this->belongsTo(User::class,'dsr_id','id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}