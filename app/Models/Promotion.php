<?php

namespace App\Models;

class Promotion extends Base
{
    protected $table = 'promotion';

    protected $primaryKey = 'promo_id';

    protected $codeName = 'promo_name';

    protected $fillable = [
        'promo_name','start_date','end_date'
    ];
}
