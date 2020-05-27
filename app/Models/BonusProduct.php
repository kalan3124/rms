<?php
namespace App\Models;

/**
 * Bonus Product
 * 
 * @property int $bnsp_id
 * @property int $bns_id
 * @property int $product_id
 * 
 * @property Bonus $bonus
 * @property Product $product
 */
class BonusProduct extends Base {
    protected $table = 'bonus_product';

    protected $primaryKey = 'bnsp_id';

    protected $fillable = [
        'bns_id',
        'product_id'
    ];

    public function bonus(){
        return $this->belongsTo(Bonus::class,'bns_id','bns_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}