<?php

namespace App\Models;

class VisitType extends Base
{
    protected $table = 'visit_type';

    protected $primaryKey = 'vt_id';

    protected $fillable = [
        'vt_name'
    ];

    protected $codeName = 'vt_name';
}
