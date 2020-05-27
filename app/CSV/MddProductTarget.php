<?php
namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\User;
use App\Models\Product;
use App\Models\Brand;
use App\Models\UserTarget;
use App\Models\UserProductTarget;

class MddProductTarget extends Base {
    protected $title = "MDD Product Targets";

    protected $columns = [
        'ut_year'=>'Year',
        'ut_month'=>'Month',
        'u_id'=>'PS Code',
        'product_id'=>'Product Code',
        'upt_qty'=>"Qty",
        'upt_value'=>"Value"
    ];

    protected $lastUser = 0 ;

    protected $tips = [
    ];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'u_id':
                if(!$value)
                    throw new WebAPIException("Please provide a user code!");
                $user = User::where((new User)->getCodeName(),"LIKE",$value)->first();
                if(!$user)
                    throw new WebAPIException("User not found! Given user code is '$value'");
                return $user->getKey();
            case 'product_id':
                if($value){
                    $product = Product::where((new Product)->getCodeName(),"LIKE",$value)->first();
                    if(!$product)
                        throw new WebAPIException("Product not found! Given code is '$value'");
                    return $product->getKey();
                } else {
                    return null;
                }
            case 'bdgt_price':
                return isset($value)?$value:0;
            default:
                return ($value<=0||!$value)?null:$value;
        }
    }

    protected function insertRow($row)
    {
            if($this->lastUser==$row['u_id']){
                $latestTarget = UserTarget::where('u_id',$row['u_id'])
                        ->where('ut_month',$row['ut_month'])
                        ->where('ut_year',$row['ut_year'])
                        ->latest()
                        ->first();
            }else {
                $latestTarget = UserTarget::create([
                    'u_id'=>$row['u_id'],
                    'ut_month'=>$row['ut_month'],
                    'ut_year'=>$row['ut_year'],
                    'ut_qty'=>0,
                    'ut_value'=>0
                ]);
            }

            UserProductTarget::create([
                'ut_id'=>$latestTarget->getKey(),
                'upt_value'=>$row['upt_value']??0,
                'upt_qty'=>$row['upt_qty']??0,
                'product_id'=>$row['product_id']
            ]);

            if($this->lastUser!=$row['u_id']){
                $this->lastUser = $row['u_id'];
            }
    }

}