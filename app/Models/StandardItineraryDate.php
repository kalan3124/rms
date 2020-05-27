<?php

namespace App\Models;

class StandardItineraryDate extends Base
{
    protected $table = 'standard_itinerary_date';

    protected $primaryKey = 'sid_id';

    protected $fillable = [
        'sid_date','si_id','bt_id','sid_mileage','sid_description'
    ];

    public function standardItinerary(){
        return $this->belongsTo(StandardItinerary::class,'si_id','si_id');
    }

    public function standardItineraryDateAreas(){
        return $this->hasMany(StandardItineraryDateArea::class,'sid_id','sid_id');
    }

    public function bataType(){
        return $this->belongsTo(BataType::class,'bt_id','bt_id');
    }

    /**
     * Alias to standard itinerary date areas
     *
     * @return void
     */
    public function areas(){
        return $this->standardItineraryDateAreas();
    }
}
