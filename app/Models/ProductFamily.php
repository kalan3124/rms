<?php

namespace App\Models;

class ProductFamily extends Base
{
    protected $table = 'product_family';

    protected $primaryKey = 'product_family_id';

    protected $fillable = [
        'product_family_name','principal_id'
    ];

    protected $codeName = 'product_family_name';

    public function product(){
        return $this->hasMany(Product::class,'brand_id','brand_id');
    }
}
