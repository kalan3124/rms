<?php

namespace App\Models;


/**
 * Writeoff Lines Model
 *
 * @property int $product_id
 * @property int $wo_id
 * @property int $db_id
 * @property int $wo_qty
 *
 * @property StockWriteOff $writeOff
 * @property Product $product
 * @property DistributorBatch $batch
 */
class StockWriteOffProduct extends Base
{
    protected $table='write_off_product';

    protected $primaryKey = 'wo_pro_id';

    protected $fillable = [
        'wo_id','product_id','db_id','wo_qty','reason'
    ];

    public function writeOff(){
        return $this->belongsTo(StockWriteOff::class,'wo_id','wo_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function batch(){
        return $this->belongsTo(DistributorBatch::class,'db_id','db_id');
    }
}
