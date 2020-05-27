<?php

namespace App\Ext;

use App\Models\Base;

class SalesmanValidParts extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'ext_salesman_valid_parts_uiv';

    protected $primarykey = 'smv_part_id';

    protected $fillable = [
        'salesman_code',
        'contract',
        'catalog_no',
        'from_date',
        'to_date',
        'last_updated_on'
    ];
}
