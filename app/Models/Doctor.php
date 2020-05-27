<?php

namespace App\Models;

class Doctor extends Base
{
    protected $dates=[
        'created_at','updated_at','deleted_at','approved_at'
    ];

    protected $table = 'doctors';

    protected $primaryKey = 'doc_id';

    protected $fillable = [
        'doc_name','doc_spc_id','slmc_no','gender','date_of_birth','phone_no','mobile_no','doc_class_id','doc_code','approved_at'
    ];

    protected $codeName = 'doc_code';

    public function doctor_speciality (){
        return $this->belongsTo(DoctorSpeciality::class,'doc_spc_id','doc_spc_id');
    }

    public function doctor_class (){
        return $this->belongsTo(DoctorClass::class,'doc_class_id','doc_class_id');
    }

    public function subTowns(){
        return $this->hasMany(DoctorSubTown::class,'doc_id','doc_id');
    }
}
