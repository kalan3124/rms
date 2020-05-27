<?php

namespace App\Models;

/**
 * Calculated bonus items when returning from distributor point
 * 
 * @property int $dis_return_id
 * @property int $product_id
 * @property int $drbi_qty
 * @property int $bns_id
 * @property int $db_id
 * 
 * @property Product $product
 * @property Bonus $bonus
 * @property DistributorBatch $batch
 * @property DistributorReturn $distributorReturn
 */
class DistributorReturnBonusItem extends Base
{
    protected $table = 'distributor_return_bonus_item';

    protected $primaryKey = 'drbi_id';

    protected $fillable = [
        'dis_return_id',
        'product_id',
        'drbi_qty',
        'bns_id',
        'db_id'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function bonus(){
        return $this->belongsTo(Bonus::class,'bns_id','bns_id');
    }

    public function batch(){
        return $this->belongsTo(DistributorBatch::class,'db_id','db_id');
    }

    public function distributorReturn(){
        return $this->belongsTo(DistributorReturn::class,'dis_return_id','dis_return_id');
    }

}
