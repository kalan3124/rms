<?php

namespace App\Models;

class GPSTracking extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'gps_tracking';

    protected $primaryKey = 'gt_id';

    protected $fillable = ["u_id","gt_lon","gt_lat","gt_btry","gt_speed","gt_time","gt_brng","gt_accu","gt_prvdr"];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}
