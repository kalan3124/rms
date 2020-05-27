<?php

namespace App\Models;

class DistributorCustomerSegment extends Base
{
    protected $table = "distributor_customer_segment";

    protected $primaryKey = 'dcs_id';

    protected $fillable=[
        'dcs_name','dcs_code'
    ];

    protected $codeName = 'dcs_code';

    public function chemist(){
        return $this->hasMany(Chemist::class,'dcs_id','dcs_id');
    }
}
