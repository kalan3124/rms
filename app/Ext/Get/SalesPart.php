<?php

namespace App\Ext\Get;

use App\Models\Product;
use App\Ext\Get\HasModel\Model;
use App\Ext\Get\HasModel\Row;
use App\Models\Principal;
use App\Models\ProductFamily;
use App\Models\TaxCode;

class SalesPart extends Model
{
    // protected $connection = 'mysql2';

    // protected $table = 'ext_sales_part_uiv';
    protected $table = 'ifsapp.EXT_SALES_PART_UIV';

    protected $primaryKey ='sales_part_no';

    public $originalModelName = Product::class;

    public $codeName = 'part_no';

    public $columnMapping = [
        'sales_part_description'=>'product_name',
        'short_description'=>'product_short_name',
        'tax_code'=>'tax_code',
        'tax_code_desc'=>'tax_code_desc'
    ];

    public function __construct()
    {

        $principle = new Row(Principal::class,'accounting_group',[
            'principal_name'=>'accounting_group_desc'
        ]);

        $family = new Row(ProductFamily::class,'product_family_desc');

        $this->subModels = [
            'principal_id'=>$principle,
            'product_family_id'=>$family
        ];
    }

    protected function createOrUpdate($data,$inst=null){
        parent::createOrUpdate($data,$inst);

        $product = Product::where('product_code', $data['part_no'])->first();
        $tax = TaxCode::where('fee_code', $data['tax_code'])->first();


        if($product){
            $product->tax_code_id = $tax?$tax->getKey():null;
            $product->save();
        }
    }

    public function newQuery($excludeDeleted = true) {
        return parent::newQuery($excludeDeleted)
            ->where('site', 'LIKE', '%WH01%');
    }
}
