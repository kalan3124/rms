<?php

namespace App\Ext;

use App\Models\Base;

class Salesman extends Base
{
    protected $revisionEnabled = false;

    protected $table = 'ext_salesman_uiv';

    protected $primaryKey = 'salesman_id';

    protected $fillable = [
        'salesman_code','name','blocked_for_use','last_updated_on'
    ];
}
