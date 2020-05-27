<?php
namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\Principal;
use App\Models\Product;
use App\Models\ProductLatestPriceInformation;
use App\Models\User;
use App\Models\UserProductTarget;
use App\Models\UserTarget;

class MddPrincipalTarget extends Base {
    protected $title = 'MDD Principal Target';

    protected $targetId = 0;

    protected $columns = [
        'month'=>'Month [YYYY-MM]',
        'u_id'=>'MR/PS Code',
        'principal_id'=>'Principal Code',
        'value'=>"Net Value"
    ];

    protected $tips = [
        "Make sure all principals are mapped to user. Otherwise you can not view the uploaded targets."
    ];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'month':
                $time = strtotime($value.'-01');
                if(!$time)
                    throw new WebAPIException("Invalid month!");
                return $time;
            case 'u_id':
                if(!$value)
                    throw new WebAPIException("Please provide a user code!");
                $user = User::where((new User)->getCodeName(),"LIKE",$value)->first();
                if(!$user)
                    throw new WebAPIException("User not found! Given user code is '$value'");
                return $user->getKey();
            case 'principal_id':
                if($value){
                    $principal = Principal::where((new Principal())->getCodeName(),"LIKE",$value)->first();
                    if(!$principal)
                        throw new WebAPIException("Principal not found! Given code is '$value'");
                    return $principal->getKey();
                } else {
                    return null;
                }
            case 'qty':
                if($value<=0||!$value)
                    throw new WebAPIException("Please enter valid qty.");

                return $value;
            default:
                return ($value<=0||!$value)?null:$value;
        }
    }

    protected function insertRow($row)
    {
        $data = [
            'ut_month'=>date('m',$row['month']),
            'ut_year'=>date('Y',$row['month']),
            'u_id'=>$row['u_id']
        ];
        $target =  UserTarget::where($data)->latest()->first();
        
        $user = User::find($row['u_id']);

        $productsByMr = Product::getByUser($user);

        $principalProducts = $productsByMr->where('principal_id',$row['principal_id']);
        $principalCount = $principalProducts->count();

        if(!$target){
            $target = UserTarget::create($data);
        } else {
            UserProductTarget::where('ut_id',$target->getKey())->where(function($query) use($row,$principalProducts){
                $query->orWhere('principal_id',$row['principal_id']);
                $query->orWhereIn('product_id',$principalProducts->pluck('product_id')->all());
            })->delete();
        }

        UserProductTarget::create([
            'ut_id'=>$target->getKey(),
            'upt_value'=>$row['value'],
            'upt_qty'=>0,
            'principal_id'=>$row['principal_id']
        ]);

        $qty = 0;
        foreach ($principalProducts as $key => $principalProduct) {
            $latestPrice = ProductLatestPriceInformation::where('product_id',$principalProduct->getKey())->latest()->first();
            $price = 0;
            if($latestPrice)
                $price = 
                    $latestPrice->lpi_bdgt_sales?
                        $latestPrice->lpi_bdgt_sales
                    :
                        $latestPrice->lpi_pg01_sales
                ;

            $productQty = $principalCount>0&&$price>0?
                    ceil($row['value']/($principalCount* $price ))
                :
                    0;

            $qty += $productQty;

            UserProductTarget::create([
                'ut_id'=>$target->getKey(),
                'upt_value'=> $principalCount? $row['value']/$principalCount:0,
                'upt_qty'=>$productQty,
                'product_id'=>$principalProduct->getKey()
            ]);
        }

        $target->ut_value = ($this->targetId!=$target->getKey()?$target->ut_value:0)+$row['value'];
        $target->ut_qty = ($this->targetId!=$target->getKey()?$target->ut_qty:0)+$qty;

        $this->targetId = $target->getKey();

        $target->save();
    }
}