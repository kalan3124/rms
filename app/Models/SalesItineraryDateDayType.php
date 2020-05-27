<?php
namespace App\Models;

/**
 * Sales Itinerary date day types
 * 
 * @property int $s_id_id
 * @property int $dt_id
 * 
 * @property DayType $dayType
 * @property SalesItineraryDate $salesItineraryDate
 */
class SalesItineraryDateDayType extends Base {
    protected $table = 'sfa_itinerary_date_has_day_type';

    protected $primaryKey = 's_iddt_id';

    protected $fillable = [ 's_id_id', 'dt_id'];

    public function dayType(){
        return $this->belongsTo(DayType::class,'dt_id','dt_id');
    }

    public function salesItineraryDate(){
        return $this->belongsTo(SalesItineraryDate::class,'s_id_id','s_id_id');
    }
}