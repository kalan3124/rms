<?php 
namespace App\Models;

/**
 * Sales order product model
 * 
 * @property int $order_pro_id
 * @property int $order_id
 * @property int $sales_qty
 * @property float $price
 * @property int $product_id
 * @property decimal $di_discount
 * 
 * @property Product $product
 * @property SfaSalesOrder $sales_order
 */
class DistributorSalesOrderProduct extends Base{

    protected $table = 'distributor_sales_order_products';

    protected $primaryKey = 'dist_order_pro_id';

    protected $fillable =[
        'dist_order_id',
        'product_id',
        'sales_qty',
        'price',
        'di_discount'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function sales_order(){
        return $this->belongsTo(DistributorSalesOrder::class,'dist_order_id','dist_order_id');
    }
}