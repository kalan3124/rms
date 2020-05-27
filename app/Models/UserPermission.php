<?php

namespace App\Models;

class UserPermission extends Base
{
    protected $table = 'user_has_permissions';

    protected $primaryKey = 'uperm_id';

    protected $fillable = ['perm_id','u_id','pg_id'];

    public function permission(){
        return $this->belongsTo(Permission::class,'perm_id','perm_id');
    }
}
