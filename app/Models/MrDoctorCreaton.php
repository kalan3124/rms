<?php 
namespace App\Models;

class MrDoctorCreaton extends Base
{
    protected $table = 'mr_doctor_creation';

    protected $primaryKey = 'mr_doc_id';

    protected $fillable = [
        'u_id',
        'doc_code',
        'doc_name',
        'slmc_no',
        'phone_no',
        'mobile_no',
        'gender',
        'date_of_birth',
        'sub_twn_id',
        'doc_class_id',
        'doc_spc_id',
        'ins_id',
        'added_date',
        'app_version'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
    public function doctor_speciality (){
        return $this->belongsTo(DoctorSpeciality::class,'doc_spc_id','doc_spc_id');
    }

    public function doctor_class (){
        return $this->belongsTo(DoctorClass::class,'doc_class_id','doc_class_id');
    }

    public function sub_town(){
        return $this->belongsTo(SubTown::class,'sub_twn_id','sub_twn_id');
    }
    
    public function institution(){
        return $this->belongsTo(Institution::class,'ins_id','ins_id');
    }
}