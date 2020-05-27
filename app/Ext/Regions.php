<?php

namespace App\Ext;

use App\Models\Base;

class Regions extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'ext_regions_uiv';

    protected $primaryKey = 'ext_region_id';

    protected $fillable = [
        'region_code','description','last_updated_on'
    ];
}
