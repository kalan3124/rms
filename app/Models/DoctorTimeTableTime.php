<?php

namespace App\Models; 

class DoctorTimeTableTime extends Base
{

    static $weekDays = [
        "sunday",
        "monday",
        "tuesday",
        "wednsday",
        "thursday",
        "friday",
        "saturday"
    ];

    protected $table = 'doctor_time_table_times';

    protected $primaryKey = 'dttt_id';

    protected $fillable = [
        'dttt_week_day','ins_id','dttt_s_time','dttt_e_time','dtt_id'
    ];

    public function doctorTimeTable(){
        return $this->belongsTo(DoctorTimeTable::class,'dtt_id','dtt_id');
    }

    public function institution(){
        return $this->belongsTo(Institution::class,'ins_id','ins_id');
    }

    public function getDayName(){
        return self::$weekDays[$this->dttt_week_day];
    }

    public static function getDayId($name){
        $reverseArr = array_flip(self::$weekDays);

        return $reverseArr[strtolower($name)];
    }

    
}
