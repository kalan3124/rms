<?php

namespace App\Ext\Get;

use LaravelTreats\Model\Traits\HasCompositePrimaryKey;
use App\Models\InventPartInStock as InventParts;
use App\Models\Product;

class InventPartInStock extends Get
{
    use HasCompositePrimaryKey;

    protected $table = 'ifsapp.EXT_INVENT_PART_IN_STOCK_UIV';

    public $hasCompositePrimary = true;

    public $primaryKey = ['contract','part_no','w_d_r_no','location_no','lot_batch_no'];

    public $hasPrimary = true;

    public function afterCreate($inst, $data)
    {
        $this->createDuplicate($data);
    }

    protected function createDuplicate($data){

        $exist = InventParts::where('contract','=',$data['contract'])
        ->where('part_no','=',$data['part_no'])
        ->where('w_d_r_no','=',$data['w_d_r_no'])
        ->where('location_no','=',$data['location_no'])
        ->where('lot_batch_no','=',$data['lot_batch_no'])->latest()->first();

        $product = Product::where('product_code','=',$data['part_no'])->latest()->first();   
        if($product)
            $data['product_id']= $product->getKey();

        if($exist){
            $exist->update($data);
        } else {
            InventParts::create($data);
        }
    }

    public function afterUpdate($inst, $data)
    {
        $this->createDuplicate($data);
    }

}
