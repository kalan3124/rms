<?php 

namespace App\Models;

class StationMileage extends Base
{
    protected $table = 'station_mileage';
    
    protected $primaryKey = 'stm_id';

    protected $fillable = [
        'u_id','exp_amount','exp_remark','stm_date','app_version','app_version','exp_date','vhtr_rate'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}