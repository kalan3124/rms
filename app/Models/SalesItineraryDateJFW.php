<?php

namespace App\Models;

/**
 * Sales Itinerary JFW Issue
 * 
 * @property int $s_id_id
 * @property int $u_id
 * @property string $s_idj_from (Deprected: They are not using this columns. These columns developed for initial requirements)
 * @property string $s_idj_to (Deprected: They are not using this columns. These columns developed for initial requirements)
 * 
 * @property SalesItineraryDate $salesItineraryDate
 * @property User $user
 */
class SalesItineraryDateJFW extends Base {
    protected $table = 'sfa_itinerary_date_jfw';

    protected $primaryKey = 's_idj_id';

    protected $fillable = ['s_id_id','u_id','s_idj_from','s_idj_to'];

    public function salesItineraryDate(){
        return $this->belongsTo(SalesItineraryDate::class,'s_id_id','s_id_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}