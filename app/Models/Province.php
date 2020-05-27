<?php

namespace App\Models;

class Province extends Base
{
    protected $table = 'province';

    protected $primaryKey = 'pv_id';

    protected $codeName = 'pv_code';

    protected $fillable = [
        'pv_name','pv_short_name','pv_code'
    ];

}
