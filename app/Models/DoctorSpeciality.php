<?php

namespace App\Models; 

class DoctorSpeciality extends Base
{
    protected $table = 'doctor_speciality';

    protected $primaryKey = 'doc_spc_id';

    protected $fillable = [
        'speciality_name','speciality_short_name'
    ];

    protected $codeName = 'speciality_short_name';

    public function doctors(){
        return $this->hasMany(Doctor::class,'doc_spc_id','doc_spc_id');
    }
}
