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
 * 
 * @property Product $product
 * @property SfaSalesOrder $sales_order
 */
class SfaSalesOrderProduct extends Base{

    protected $table = 'sfa_sales_order_product';

    protected $primaryKey = 'order_pro_id';

    protected $fillable =[
        'order_id',
        'product_id',
        'sales_qty',
        'price'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function sales_order(){
        return $this->belongsTo(SfaSalesOrder::class,'order_id','order_id');
    }
}