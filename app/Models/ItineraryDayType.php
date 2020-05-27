<?php

namespace App\Models;

class ItineraryDayType extends Base
{
    protected $table = 'itinerary_day_type';

    protected $primaryKey = 'idt_id';

    protected $fillable = ['id_id','dt_id'];

    public function itineraryDate (){
        return $this->belongsTo(ItineraryDate::class,'id_id','id_id');
    }

    public function dayType(){
        return $this->belongsTo(DayType::class,'dt_id','dt_id');
    }
}
