<?php

namespace App\Models;

class ItineraryDateChangeArea extends Base {
    protected $table = 'itinerary_date_change_areas';

    protected $primaryKey = 'idca_id';

    protected $fillable = ['idc_id','sub_twn_id'];

    public function subTown(){
        return $this->belongsTo(SubTown::class,'sub_twn_id','sub_twn_id');
    }

    public function itineraryDateChange(){
        return $this->belongsTo(ItineraryDateChange::class,'idc_id','idc_id');
    }

}