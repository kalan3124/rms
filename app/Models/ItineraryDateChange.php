<?php

namespace App\Models;

class ItineraryDateChange extends Base {
    protected $table = 'itinerary_date_changes';

    protected $primaryKey = 'idc_id';

    protected $fillable = ['u_id','idc_date','idc_mileage','bt_id','idc_aprvd_u_id','idc_aprvd_at','remark','description'];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function bataType(){
        return $this->belongsTo(BataType::class,'bt_id','bt_id');
    }

    public function approver(){
        return $this->belongsTo(User::class,'idc_aprvd_u_id','id');
    }

    public function areas(){
        return $this->hasMany(ItineraryDateChangeArea::class,'idc_id','idc_id');
    }

}