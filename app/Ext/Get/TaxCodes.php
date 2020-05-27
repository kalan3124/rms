<?php

namespace App\Ext\Get;

use App\Models\TaxCode;

class TaxCodes extends Get
{
    // protected $connection = 'mysql2';

    // protected $table = 'ext_tax_code_uiv';
    protected $table = 'ifsapp.EXT_TAX_CODES_UIV';

    public $hasPrimary=true;

    public $hasCompositePrimary = true;

    public $primaryKey = ['company','fee_code'];

    public function afterCreate($inst, $data)
    {
        $this->createDuplicate($data);
    }

    protected function createDuplicate($data){

        $exist = TaxCode::where('company','=',$data['company'])->where('fee_code','=',$data['fee_code'])->latest()->first();

        if($exist){
            $exist->update($data);
        } else {
            TaxCode::create($data);
        }
    }

    public function afterUpdate($inst, $data)
    {
        $this->createDuplicate($data);
    }
}
