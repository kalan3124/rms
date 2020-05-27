<?php

namespace App\Models;

/**
 * Return Items for the return order
 * 
 * @property int $rsn_id
 * @property int $dis_return_id
 * @property int $product_id
 * @property int $dri_qty
 * @property float $dri_price
 * @property int $dri_bns_qty (Deprecated: Use DistributorReturnBonusItem Model)
 * @property int $dri_is_salable 1=Yes, 0=No
 * @property int $db_id
 * @property float $dri_dis_percent Discount percentage
 * 
 * @property Product $product
 * @property DistributorBatch $batch
 * @property DistributorReturn $distributorReturn
 */
class DistributorReturnItem extends Base
{
    protected $table = 'distributor_return_item';

    protected $primaryKey = 'dri_id';

    protected $fillable = [
        'rsn_id',
        'dis_return_id',
        'product_id',
        'dri_qty',
        'dri_price',
        'dri_bns_qty',
        'dri_is_salable',
        'db_id',
        'dri_dis_percent',
        'unit_price_no_tax'
    ];

    public function batch(){
        return $this->belongsTo(DistributorBatch::class,'db_id','db_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function distributorReturn(){
        return $this->belongsTo(DistributorReturn::class,'dis_return_id','dis_return_id');
    }

}
