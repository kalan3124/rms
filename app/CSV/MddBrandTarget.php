<?php
namespace App\CSV;

use App\Models\Brand;
use App\Exceptions\WebAPIException;
use App\Models\Product;
use App\Models\ProductLatestPriceInformation;
use App\Models\UserTarget;
use App\Models\UserProductTarget;
use App\Models\User;

class MddBrandTarget extends Base {
    protected $title = "MDD Brand Target";

    protected $targetId = 0;

    protected $columns = [
        'month'=>'Month [YYYY-MM]',
        'rep_id'=>'MR/PS Code',
        'brand_id'=>'Brand Code',
        'value'=>'Net Value'
    ];

    protected $tips = [
        "Make sure all brands are mapped to user. Otherwise you can not view the uploaded targets."
    ];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'rep_id':
                if(!$value)
                    throw new WebAPIException("Please provide a MR/PS code!");
                $user = User::where((new User)->getCodeName(),"LIKE",$value)->first();
                if(!$user)
                    throw new WebAPIException("MR/PS not found! Given user code is '$value'");
                return $user->getKey();
            case 'brand_id':
                if($value){
                    $brand = Brand::where((new Brand)->getCodeName(),"LIKE",$value)->first();
                    if(!$brand)
                        throw new WebAPIException("Brand not found! Given code is '$value'");
                    return $brand->getKey();
                } else {
                    return null;
                }
            case 'value':
                return isset($value)?$value:0;
            case 'month':
                if(!strtotime($value.'-01'))
                    throw new WebAPIException("Invalid month given. Valid month pattern is YYYY-MM. But '$value' is the given month. ");

                return strtotime($value.'-01');
            default:
                return null;
        }
    }

    protected function insertRow($row)
    {
            $data = [
                'ut_month'=>date('m',$row['month']),
                'ut_year'=>date('Y',$row['month']),
                'u_id'=>$row['rep_id']
            ];
            $target =  UserTarget::where($data)->latest()->first();
              
            $user = User::find($row['rep_id']);

            $productsByMr = Product::getByUser($user);

            $brandProducts = $productsByMr->where('brand_id',$row['brand_id']);
            $brandCount = $brandProducts->count();

            if(!$target){
                $target = UserTarget::create($data);
            } else {
                UserProductTarget::where('ut_id',$target->getKey())->where(function($query) use($row,$brandProducts){
                    $query->orWhere('principal_id',$row['brand_id']);
                    $query->orWhereIn('product_id',$brandProducts->pluck('product_id')->all());
                })->delete();
            }

            UserProductTarget::create([
                'ut_id'=>$target->getKey(),
                'upt_value'=>$row['value'],
                'upt_qty'=>0,
                'brand_id'=>$row['brand_id']
            ]);

            $qty = 0;
            foreach ($brandProducts as $key => $brandProduct) {
                $latestPrice = ProductLatestPriceInformation::where('product_id',$brandProduct->getKey())->latest()->first();

                $price = 0;
                if($latestPrice)
                    $price = 
                        $latestPrice->lpi_bdgt_sales?
                            $latestPrice->lpi_bdgt_sales
                        :
                            $latestPrice->lpi_pg01_sales;

                $productQty = $brandCount>0&&$price>0?
                        ceil($row['value']/($brandCount* $price ))
                    :
                        0;

                $qty += $productQty;

                UserProductTarget::create([
                    'ut_id'=>$target->getKey(),
                    'upt_value'=> $brandCount? $row['value']/$brandCount:0,
                    'upt_qty'=>$productQty,
                    'product_id'=>$brandProduct->getKey()
                ]);
            }

            $target->ut_value = ($this->targetId!=$target->getKey()?$target->ut_value:0)+$row['value'];
            $target->ut_qty = ($this->targetId!=$target->getKey()?$target->ut_qty:0)+$qty;

            $this->targetId = $target->getKey();

            $target->save();
    }
}