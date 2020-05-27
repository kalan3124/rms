<?php
namespace App\Form;

use Illuminate\Support\Facades\DB;

class DistributorBatch extends Form{

    protected $title='Distributor Batch';

    protected $dropdownDesplayPattern = 'db_code';

    public function beforeSearch($query, $values)
    {
        $query->with('product');
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('db_code')->setLabel("Code")->isUpperCase();
        $inputController->number('db_price')->setLabel("Price");
        $inputController->number('db_tax_price')->setLabel('Tax Price');
        $inputController->ajax_dropdown('product_id')->setLabel("Product")->setLink('product');
        $inputController->date('db_expire')->setLabel("Expire");

        $inputController->setStructure([
            ["db_code",'db_expire'],
            ["product_id","db_price",'db_tax_price']
        ]);
    }

    public function filterDropdownSearch($query, $where)
    {
        if(isset($where['distributor'])){
            
            if(isset($where['distributor']['value'])||isset($where['distributor'])){
                $productId = null;

                if(isset($where['distributor']['value']))
                    $disId = $where['distributor']['value'];
                else
                    $disId = $where['distributor'];

                if(isset($where['product'])&&isset($where['product']['value'])){
                    $productId = $where['product']['value'];
                    unset($where['product']);
                } else if (isset($where['product'])) {
                    $productId = $where['product'];
                    unset($where['product']);
                }

                $stocksQuery =  DB::table('distributor_stock AS ds')
                    ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                    ->select(['db.db_id'])
                    ->where('ds.dis_id',$disId)
                    ->whereDate('db.db_expire','>=',date('Y-m-d'))
                    ->groupBy('db.db_id');

                if(isset($productId))
                    $stocksQuery->where('db.product_id',$productId);

                $stocks = $stocksQuery->get();

                $query->whereIn('db_id',$stocks->pluck('db_id')->all());
            }

            unset($where['distributor']);
        }



        if(isset($where['product'])&&isset($where['product']['value'])){
            $productId = $where['product']['value'];

            $query->whereIn('product_id',$productId);
            unset($where['product']);
        }
    }

/**
     * Returning only privileged actions
     *
     * @return Action[]
     */
    public function getPrivilegedActions(){
        return ['update'];
    }

}