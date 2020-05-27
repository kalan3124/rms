<?php

namespace App\Models;

class UnproductiveVisit extends Base
{
    protected $table = 'unproductive_visit';

    protected $primaryKey = 'un_visit_id';

    protected $fillable = [
        'un_visit_no',
        'doc_id',
        'chemist_id',
        'u_id',
        'visit_type',
        'is_shedule',
        'shedule_id',
        'reason_id',
        'btry_lvl',
        'lat',
        'lon',
        'unpro_time',
        'visited_place',
        'app_version',
        'hos_stf_id'
    ];

    public function doctor(){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function reason(){
        return $this->belongsTo(Reason::class,'reason_id','rsn_id');
    }

    public function other_hos_staff(){
        return $this->belongsTo(OtherHospitalStaff::class,'hos_stf_id','hos_stf_id');
    }
}
