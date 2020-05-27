<?php

namespace App\Models;

class UserAttendance extends Base
{
    protected $table = 'user_attendance';

    protected $primaryKey = 'att_id';

    protected $dates = ['check_in_time','updated_at','created_at','deleted_at','check_out_time'];

    protected $fillable = [
        'u_id','check_in_lat','check_in_lon','check_in_time','check_in_mileage','check_in_battery','check_in_loc_type','check_out_lat','check_out_lon','check_out_time','check_out_mileage','check_out_battery','check_out_loc_type','checkout_status','app_version'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
    /**
     * Checking the current attendance is day ended or not
     * 
     * @return bool
     */
    public function isDayEnded(){
        return $this->checkout_status==1;
    }
}

