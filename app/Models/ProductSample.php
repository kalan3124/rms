<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSample extends Model
{
    protected $table = 'product_samples';

    protected $primaryKey = 'id';

    protected $fillable = [
        'product_id','s_product_code','s_product_name'
    ];

    // protected $codeName = 'product_family_name';

    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
}
