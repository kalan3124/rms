<?php

namespace App\Models;

class HospitalStaffCategory extends Base
{
    protected $table = 'hospital_staff_category';

    protected $primaryKey = 'hos_stf_cat_id';

    protected $fillable = [
        'hos_stf_cat_name','hos_stf_cat_short_name'
    ];

    protected $codeName = 'hos_stf_cat_short_name';

    public function other_hospital_staff (){
        return $this->hasMany(OtherHospitalStaff::class,'hos_stf_cat_id','hos_stf_cat_id');
    }
}
