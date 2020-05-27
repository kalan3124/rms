<?php
namespace App\Models;

/**
 * This table contains lates price informations of all products.
 *
 * @property int $product_id
 * @property float $lpi_bdgt_sales
 * @property float $lpi_pg01_sales
 * @property int $year
 *
 * @property Product $product
 */
class ProductLatestPriceInformation extends Base {
    protected $table='latest_price_informations';

    protected $primaryKey = 'lpi_id';

    protected $fillable=[
        'product_id',
        'lpi_bdgt_sales',
        'lpi_pg01_sales',
        'year'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}
