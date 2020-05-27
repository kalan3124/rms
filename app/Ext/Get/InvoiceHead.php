<?php

namespace App\Ext\Get;

use LaravelTreats\Model\Traits\HasCompositePrimaryKey;
use App\Models\Invoice;
use App\Models\Chemist;

class InvoiceHead extends Get
{
    use HasCompositePrimaryKey;
    
    protected $table = 'ifsapp.EXT_INVOICE_HEAD_UIV';

    public $hasCompositePrimary = true;

    public $primaryKey = ['site','invoice_no'];

    public $hasPrimary = true;

    public function afterCreate($inst, $data)
    {
        $this->createDuplicate($data);
    }

    protected function createDuplicate($data){
        $exist = Invoice::where('site','=',$data['site'])
            ->where('invoice_no','=',$data['invoice_no'])->latest()->first();
        
        $chemist = Chemist::where('chemist_code','=',$data['customer_no'])->first();
        if($chemist)
            $data['chemist_id'] = $chemist->getKey();

        

        if($exist){
            $exist->update($data);
        } else {
            Invoice::create($data);
        }
    }

    public function afterUpdate($inst, $data)
    {
        $this->createDuplicate($data);
    }
}
