<?php

namespace App\Models;

class Itinerary extends Base
{
    protected $table = 'itinerary';

    protected $primaryKey = 'i_id';

    protected $fillable = ['i_year','i_month','rep_id','fm_id','i_aprvd_u_id', 'i_aprvd_at'];

    public function medicalRep (){
        return $this->belongsTo(User::class,'rep_id','id');
    }

    public function fieldManager(){
        return $this->belongsTo(User::class,'fm_id','id');
    }

    public function itineraryDates(){
        return $this->hasMany(ItineraryDate::class,'i_id','i_id');
    }

    public function approver(){
        return $this->belongsTo(User::class,'i_aprvd_u_id','id');
    }
}
