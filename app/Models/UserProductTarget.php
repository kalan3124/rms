<?php

namespace App\Models;

/**
 * Child model of user target class
 * 
 * @property int $upt_id Auto Increment Id
 * @property float $upt_value Value Target
 * @property int $upt_qty Qty Target
 * @property int $product_id
 * @property int $brand_id
 * @property int $principal_id
 * @property int $ut_id
 * 
 * @property Brand $brand
 * @property Principal $principal
 * @property Product $product
 * @property UserTarget $userTarget
 */
class UserProductTarget extends Base
{
    protected $table = 'user_product_target';

    protected $primaryKey = 'upt_id';

    protected $fillable = ['ut_id','upt_value','upt_qty','product_id','brand_id','principal_id'];

    public function userTarget(){
        return $this->belongsTo(UserTarget::class,'ut_id','ut_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function brand(){
        return $this->belongsTo(Brand::class,'brand_id','brand_id');
    }

    public function principal(){
        return $this->belongsTo(Principal::class,'principal_id','principal_id');
    }
}
