<?php

namespace App\Models;

class DoctorPromotion extends Base{
    protected $table = 'doctor_promotion';

    protected $primaryKey = 'dpromo_id';

    protected $fillable = ['doc_id','promo_id'];

    public function doctor(){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }

    public function promotion(){
        return $this->belongsTo(Promotion::class,'promo_id','promo_id');
    }
}