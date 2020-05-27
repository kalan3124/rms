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
 * @property DCSalesItineraryDate $dcsalesItineraryDate
 * @property User $user
 */
class DCSalesItineraryDateJFW extends Base {
    protected $table = 'sdc_itinerary_date_jfw';

    protected $primaryKey = 'sdc_idj_id';

    protected $fillable = ['sdc_id_id','u_id','sdc_idj_from','sdc_idj_to'];

    public function dcSalesItineraryDate(){
        return $this->belongsTo(DCSalesItineraryDate::class,'sdc_id_id','sdc_id_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}
