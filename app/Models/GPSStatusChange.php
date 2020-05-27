<?php
namespace App\Models;

class GPSStatusChange extends Base {
    protected $table = 'gps_status_change';

    protected $primaryKey = 'gsc_id';

    protected $fillable = [
        'u_id',
        'gsc_lon',
        'gsc_lat',
        'gsc_btry',
        'gsc_speed',
        'gsc_time',
        'gsc_brng',
        'gsc_accu',
        'gsc_prvdr',
        'gsc_status',
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}