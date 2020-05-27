<?php 

namespace App\Models;

class SfaUserLogin extends Base
{
    protected $table = 'sfa_user_login';

    protected $primaryKey = 'sfa_login_id';

    protected $fillable = [
        'u_id','login_date'
    ];

    public function user(){
        return $this->hasOne(User::class,'id','u_id');
    }
}