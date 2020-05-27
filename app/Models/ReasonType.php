<?php

namespace App\Models;

class ReasonType extends Base
{
    protected $table = 'reason_types';

    protected $primaryKey = 'rsn_tp_id';

    protected $codeName = 'rsn_type';

    protected $fillable = [
        'rsn_type'
    ];
}
