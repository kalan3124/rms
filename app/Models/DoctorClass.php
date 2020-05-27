<?php

namespace App\Models;

class DoctorClass extends Base
{
    protected $table = 'doctor_classes';

    protected $primaryKey = 'doc_class_id';

    protected $fillable = [
        'doc_class_name'
    ];

    protected $codeName = 'doc_class_name';

    public function doctors (){
        return $this->hasMany(Doctor::class,'doc_class_id','doc_class_id');
    }
}
