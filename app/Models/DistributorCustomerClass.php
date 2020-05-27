<?php

namespace App\Models;

class DistributorCustomerClass extends Base
{
    protected $table = "distributor_customer_class";

    protected $primaryKey = 'dcc_id';

    protected $fillable=[
        'dcc_name','dcc_code'
    ];

    protected $codeName = 'dcc_code';

    public function chemist(){
        return $this->hasMany(Chemist::class,'dcc_id','dcc_id');
    }
}
