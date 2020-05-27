<?php
namespace App\Models;

/**
 * Distributor Batches
 * 
 * @property int $db_id
 * @property string $db_code
 * @property float $db_price
 * @property int $product_id
 * @property int $db_expire
 * @property float $db_tax_price This price is using for VAT customers
 * 
 * @property Product $product
 */
class DistributorBatch extends Base {
    protected $table = 'distributor_batches';

    protected $primaryKey = 'db_id';

    protected $fillable = [
        'db_code',
        'db_price',
        'product_id',
        'db_expire',
        'db_tax_price'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}