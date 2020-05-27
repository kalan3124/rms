<?php

namespace App\Models;

class ProductiveVisit extends Base
{
    protected $table = 'productive_visit';

    protected $primaryKey = 'pro_visit_id';

    protected $fillable = [
        'pro_visit_no',
        'doc_id',
        'chemist_id',
        'hos_stf_id',
        'u_id',
        'visit_type',
        'is_shedule',
        'shedule_id',
        'audio_path',
        'promo_id',
        'promo_remark',
        'pro_summary',
        'join_field_id',
        'pro_start_time',
        'pro_end_time',
        'lat',
        'lon',
        'btry_lvl',
        'visited_place',
        'app_version'
    ];

    public function doctor(){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }

    public function otherHospitalStaff(){
        return $this->belongsTo(OtherHospitalStaff::class,'hos_stf_id','hos_stf_id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function promotion(){
        return $this->belongsTo(Promotion::class,'promo_id','promo_id');
    }

    public function second_user(){ //get join field user
        return $this->belongsTo(User::class,'join_field_id','id');
    }

    public function visitType(){
        return $this->belongsTo(VisitType::class,'visited_place','vt_id');
    }

    public function details(){
        return $this->hasMany(ProductiveSampleDetails::class,'pro_visit_id','pro_visit_id');
    }
}
