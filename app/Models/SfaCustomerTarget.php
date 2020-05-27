<?php

namespace App\Models;

class SfaCustomerTarget extends Base{

     protected $table = 'sfa_customer_target';

    protected $primaryKey = 'sfa_cus_target_id';

    protected $fillable = [
        'sfa_cus_code','sfa_sr_code','sfa_year','sfa_month','sfa_target'
    ];

    public function chemist(){
        return $this->hasOne(Chemist::class,'chemist_id','sfa_cus_code');
    }

}
?>