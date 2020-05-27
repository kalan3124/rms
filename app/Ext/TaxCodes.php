<?php

namespace App\Ext;

use App\Models\Base;

class TaxCodes extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'ext_tax_code_uiv';

    protected $primaryKey = 'tax_id';

    protected $fillable = [
        'company','fee_code','description','fee_rate','valid_from','valid_until','fee_type','last_updated_on'
    ];
}
