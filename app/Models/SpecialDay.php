<?php

namespace App\Models;

class SpecialDay extends Base
{
    protected $table = 'special_days';

    protected $primaryKey = 'sd_id';

    protected $fillable = [
        'sd_date','sd_name'
    ];

    protected $codeName = 'sd_date';
}
