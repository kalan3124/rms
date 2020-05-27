<?php

namespace App\Models;

class PermissionGroupUser extends Base
{
    protected $table = 'permission_group_user';

    protected $primaryKey = 'pgu_id';

    protected $fillable = [
        'pg_id','u_id','u_tp_id'
    ];
    
    public function permissionGroup(){
        return $this->belongsTo(PermissionGroup::class,'pg_id','pg_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function user_type(){
        return $this->belongsTo(UserType::class,'u_tp_id','u_tp_id');
    }
}
