<?php
namespace App\Models;

class BonusFreeProduct extends Base {
    protected $table = 'bonus_free_product';

    protected $primaryKey = 'bnsfp_id';

    protected $fillable = [
        'product_id',
        'bns_id'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function bonus(){
        return $this->belongsTo(Bonus::class,'bns_id','bns_id');
    }
}