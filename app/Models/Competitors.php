<?php

namespace App\Models;

class Competitors extends Base {
    protected $table = 'competitors';

    protected $primaryKey = 'cmp_id';

    protected $fillable = [
        'cmp_name','cmp_address'
    ];
}
