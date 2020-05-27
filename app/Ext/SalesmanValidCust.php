<?php

namespace App\Ext;

use App\Models\Base;

class SalesmanValidCust extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'ext_salesman_valid_cust_uiv';

    protected $primaryKey = 'smv_cust_id';

    protected $fillable = [
        'salesman_code',
        'customer_id',
        'from_date',
        'to_date',
        'last_updated_on'
    ];
}
