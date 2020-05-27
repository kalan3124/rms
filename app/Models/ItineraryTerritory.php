<?php

namespace App\Models;

class ItineraryTerritory extends Base
{
    protected $table = 'itinerary_territory';

    protected $primaryKey = 'it_id';

    protected $fillable = ['id_id','twn_id','ar_id','dis_id','pv_id'];

    protected $hierarchy = ['town','area','district','province'];

    public function itineraryDate(){
        return $this->belongsTo(ItineraryDate::class,'id_id','id_id');
    }

    public function area (){
        return $this->belongsTo(Area::class,'ar_id','ar_id');
    }

    public function district (){
        return $this->belongsTo(District::class,'dis_id','dis_id');
    }

    public function province (){
        return $this->belongsTo(Province::class,'pv_id','pv_id');
    }
    
    public function town(){
        return $this->belongsTo(Town::class,'twn_id','twn_id');
    }
}
