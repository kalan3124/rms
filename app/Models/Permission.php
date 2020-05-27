<?php

namespace App\Models;

class Permission extends Base
{
    protected $table = 'permissions';

    protected $primaryKey = 'perm_id';

    protected $fillable = [
        'perm_code','perm_name'
    ];
}
