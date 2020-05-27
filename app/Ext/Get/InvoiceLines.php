<?php

namespace App\Ext\Get;

use App\Models\Chemist;
use LaravelTreats\Model\Traits\HasCompositePrimaryKey;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\Invoice;

class InvoiceLines extends Get
{
    use HasCompositePrimaryKey;

    protected $table = 'ifsapp.EXT_INVOICE_LINES_UIV';

    public $hasCompositePrimary = true;

    public $primaryKey = ['catalog_no','invoice_no','series_id'];

    public $hasPrimary = true;


    public function afterCreate($inst, $data)
    {
        $this->createDuplicate($data);
    }

    protected function createDuplicate($data){
        $exist = InvoiceLine::where('catalog_no','=',$data['catalog_no'])
            ->where('invoice_id','=',$data['invoice_id'])->latest()->first();
        
        $product = Product::where('product_code','=',$data['catalog_no'])->first();
        if($product)
            $data['product_id'] = $product->getKey();

        $chemist = Chemist::where('chemist_code','=',$data['identity'])->first();
        if($chemist)
            $data['chemist_id'] = $chemist->getKey();

        $invoice = Invoice::where('invoice_no','=',$data['invoice_no'])
            ->where('site','=',$data['series_id'])
            ->latest()->first();

        if($invoice)
            $data['inv_head_id']= $invoice->getKey();
        

        if($exist){
            $exist->update($data);
        } else {
            InvoiceLine::create($data);
        }
    }

    public function afterUpdate($inst, $data)
    {
        $this->createDuplicate($data);
    }
}
