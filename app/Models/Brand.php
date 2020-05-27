<?php

namespace App\Models;

/**
 * Product Brands
 * 
 * @property int $brand_id
 * @property string $brand_name
 * @property int $product_family_id Product Family Relation
 * 
 * @property Illuminate\Database\Eloquent\Collection|Product[] $product
 */
class Brand extends Base
{
    protected $table = 'brand';

    protected $primaryKey = 'brand_id';

    protected $fillable = [
        'brand_name','product_family_id'
    ];

    public function product(){
        return $this->hasMany(Product::class,'brand_id','brand_id');
    }

    protected $codeName = 'brand_name';
}
