<?php
namespace App\Models;

class VehicleTypeRate extends Base{
    protected $table = 'vehicle_type_rates';

    protected $primaryKey = 'vhtr_id';

    protected $fillable = ['vhtr_rate','u_tp_id','vht_id','vhtr_srt_date'];

    public function vehicle_type(){
        return $this->belongsTo(VehicleType::class,'vht_id','vht_id');
    }

    public function user_type(){
        return $this->belongsTo(UserType::class,'u_tp_id','u_tp_id');
    }
}