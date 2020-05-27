<?php

namespace App\Ext;

use App\Models\Base;

class Site extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'ext_site_uiv';

    protected $primaryKey = 'site_id';

    protected $fillable = [
        'contract',
        'description',
        'company',
        'country',
        'country_db',
        'address1',
        'address2',
        'sfa_coordinator',
        'last_updated_on'
    ];
}
