<?php

namespace App\Models;

/**
 * Stock Adjustment Line Model
 * 
 * @property int $stk_adj_pro_id Auto Increment
 * @property int $product_id
 * @property int $stk_adj_id Foreing to Stock Adjustment Parent
 * @property int $db_id
 * @property int $stk_adj_qty Adjusted Qty
 * 
 * @property StockAdjusment $stockAdjustment
 * @property Product $product
 * @property DistributorBatch $batch
 */
class StockAdjusmentProduct extends Base
{
    protected $table='stock_adjusment_product';

    protected $primaryKey = 'stk_adj_pro_id';

    protected $fillable = [
        'stk_adj_id','product_id','db_id','stk_adj_qty','reason'
    ];

    public function stockAdjustment(){
        return $this->belongsTo(StockAdjusment::class,'stk_adj_id','stk_adj_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function batch(){
        return $this->belongsTo(DistributorBatch::class,'db_id','db_id');
    }
}
