<?php

namespace App\Models;

class PermissionGroup extends Base
{
    protected $table = 'permission_group';

    protected $primaryKey = 'pg_id';

    protected $fillable = [
        'pg_name','pg_code'
    ];

    public function userPermissions(){
        return $this->hasMany(UserPermission::class,'pg_id','pg_id');
    }
}
