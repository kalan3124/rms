<?php

namespace App\Models;

class UserType extends Base
{
    protected $table = 'user_types';

    protected $primaryKey = 'u_tp_id';

    protected $fillable = [
        'user_type','main_user_type'
    ];

    protected $codeName = 'user_type';

    public function users (){
        return $this->hasMany(User::class,'u_tp_id','u_tp_id');
    }
}
