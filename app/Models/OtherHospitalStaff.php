<?php

namespace App\Models;

class OtherHospitalStaff extends Base
{
    protected $table = 'other_hospital_staff';

    protected $primaryKey = 'hos_stf_id';

    protected $fillable = [
        'hos_stf_name','gender','date_of_birth','phone_no','mobile_no','hos_stf_cat_id','sub_twn_id','ins_id','hos_stf_code'
    ];

    protected $codeName = 'hos_stf_code';

    public function hospital_staff_category(){
        return $this->belongsTo(HospitalStaffCategory::class,'hos_stf_cat_id','hos_stf_cat_id');
    }

    public function sub_town (){
        return $this->belongsTo(SubTown::class,'sub_twn_id','sub_twn_id');
    }

    public function institution (){
        return $this->belongsTo(Institution::class,'ins_id','ins_id');
    }
}
