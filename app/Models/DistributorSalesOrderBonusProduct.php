<?php
namespace App\Models;

/**
 * Bonus Items sent with distributor sales order
 * 
 * @property int $dist_order_id
 * @property int $dsobp_qty
 * @property int $bns_id
 * @property int $product_id
 * 
 * @property Bonus $bonus
 * @property Product $product
 * @property DistributorSalesOrder $salesOrder
 */
class DistributorSalesOrderBonusProduct extends Base {
    protected $table = 'distributor_sales_order_bonus_products';

    protected $primaryKey = 'dsobp_id';

    protected $fillable = [
        'dist_order_id',
        'dsobp_qty',
        'bns_id',
        'product_id',
    ];

    public function bonus(){
        return $this->belongsTo(Bonus::class,'bns_id','bns_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function salesOrder(){
        return $this->belongsTo(DistributorSalesOrder::class,'dist_order_id','dist_order_id');
    }
}