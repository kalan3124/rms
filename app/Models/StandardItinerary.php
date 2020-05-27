<?php

namespace App\Models;

class StandardItinerary extends Base
{
    protected $table = 'standard_itinerary';

    protected $primaryKey = 'si_id';

    protected $fillable = ['u_id'];

    public function user (){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function standardItineraryDates(){
        return $this->hasMany(StandardItineraryDate::class,'si_id','si_id');
    }

}
