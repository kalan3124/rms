<?php

namespace App\Models;

class DcCustomer extends Base
{
    protected $table = 'dc_route_customer';

    protected $primaryKey = 'id';

    protected $fillable = ['u_id','chemist_id'];

    protected $codeName = 'dt_code';

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function chemists(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }
}
