<?php

namespace App\Ext\Get;

use LaravelTreats\Model\Traits\HasCompositePrimaryKey;
use App\Models\SalesmanValidPart;
use App\Models\User;
use App\Models\Product;

class SalesmanValidParts extends Get
{

    use HasCompositePrimaryKey;

    protected $table = 'ifsapp.EXT_SALESMAN_VALID_PARTS_UIV';

    public $hasCompositePrimary = true;

    public $primaryKey = ['salesman_code','catalog_no'];

    public $hasPrimary = true;

    public function afterCreate($inst, $data)
    {
        $this->createDuplicate($data);
    }

    protected function createDuplicate($data){
        $exist = SalesmanValidPart::where('salesman_code','=',$data['salesman_code'])
            ->where('catalog_no','=',$data['catalog_no'])->latest()->first();

        $user = User::where('u_code','=',$data['salesman_code'])
            ->where('u_tp_id','=',config("shl.sales_rep_type"))
            ->latest()->first();   
        if($user)
            $data['u_id']= $user->getKey();

        $product = Product::where('product_code','=',$data['catalog_no'])->latest()->first();   
        if($product)
            $data['product_id']= $product->getKey();
            
        if($exist){
            $exist->update($data);
        } else {
            SalesmanValidPart::create($data);
        }
    }

    public function afterUpdate($inst, $data)
    {
        $this->createDuplicate($data);
    }
}
