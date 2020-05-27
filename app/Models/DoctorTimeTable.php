<?php

namespace App\Models; 

class DoctorTimeTable extends Base
{
    protected $table = 'doctor_time_table';

    protected $primaryKey = 'dtt_id';

    protected $fillable = [
        'doc_id'
    ];

    public function doctor(){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }

    public function doctorTimeTableTimes(){
        return $this->hasMany(DoctorTimeTableTime::class,'dtt_id','dtt_id');
    }
}
