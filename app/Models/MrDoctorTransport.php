<?php 
namespace App\Models;

class MrDoctorTransport extends Base
{
    protected $table = 'mr_doctor_transport';

    protected $primaryKey = 'mr_dt_id';

    protected $fillable = [
        'u_id',
        'doc_id',
        'bata_rsn_id',
        'exp_rsn_id',
        'start_mileage',
        'end_mileage',
        'start_lat',
        'start_lon',
        'start_loc_type',
        'end_loc_type',
        'end_lat',
        'end_lon',
        'start_time',
        'end_time',
        'app_version'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
    public function doctor(){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }
    public function bata_reason(){
        return $this->belongsTo(Reason::class,'bata_rsn_id','rsn_id');
    }
    public function exp_reason(){
        return $this->belongsTo(Reason::class,'exp_rsn_id','rsn_id');
    }
}