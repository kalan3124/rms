<?php

namespace App\Ext\Get;

use App\Models\Product;
use App\Models\ProductLatestPriceInformation;
use App\Models\SalesPriceLists;

class SalesPriceList extends Get
{
    // protected $connection = 'mysql2';

    protected $table = 'ifsapp.EXT_SALES_PRICE_LIST_UIV';
    // protected $table = 'ext_sales_price_list_uiv';

    protected $primaryKey = ['catalog_no','price_list_no'];

    public $hasCompositePrimary = true;

    /**
     * Updating latest price in our tables
     *
     * @param \App\Models\Base $inst
     * @return void
     */
    protected function updateLatestPrices($inst,$data){
        if(!is_object($inst))
            return null;
        if($inst->sales_price==0)
            return null;

        $product = Product::where('product_code','LIKE',$inst->catalog_no)->latest()->first();

        if($inst->base_price_site=='WH01' && $inst->state=='Active'){
            $month = date('m',strtotime($inst->last_updated_on));
            $year = date('Y',strtotime($inst->last_updated_on));

            if($product){
                $latestPrice = ProductLatestPriceInformation::firstOrCreate([
                    'product_id'=>$product->getKey(),
                    'year'=> $month<4?$year-1: $year
                ]);

                if($inst->price_list_no=='BDGT'){
                    $latestPrice->lpi_bdgt_sales = $inst->sales_price;
                } else if($inst->price_list_no=='PG01'){
                    $latestPrice->lpi_pg01_sales = $inst->sales_price;
                }

                $latestPrice->save();
            }
        }
    }

    protected function createDuplicate($data){

        if($data['state']=='Active'){
            $exist = SalesPriceLists::where('price_list_no','=',$data['price_list_no'])
                ->where('catalog_no','=',$data['catalog_no'])
                ->latest()->first();

            $product = Product::where('product_code','=',$data['catalog_no'])->latest()->first();
            if($product)
                $data['product_id']= $product->getKey();

            if($exist){
                $exist->update($data);
            } else {
                SalesPriceLists::create($data);
            }
        }
    }

    /**
     * Trigger an action after created
     *
     * @param \App\Models\Base $inst
     * @param array $data
     * @return void
     */
    public function afterUpdate($inst,$data){
       $this->updateLatestPrices($inst,$data);
       $this->createDuplicate($data);
    }
    /**
     * Trigger an action after create
     *
     * @param \App\Models\Base $inst
     * @param array $data
     * @return void
     */
    public function afterCreate($inst,$data){
        $this->updateLatestPrices($inst,$data);
        $this->createDuplicate($data);
    }

    public function newQuery($excludeDeleted = true) {
        return parent::newQuery($excludeDeleted)
            ->where('state', '=', 'Active');
    }
}
