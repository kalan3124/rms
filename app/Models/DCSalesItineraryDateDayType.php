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
class DCSalesItineraryDateDayType extends Base {
    protected $table = 'sdc_itinerary_date_has_day_type';

    protected $primaryKey = 'sdc_iddt_id';

    protected $fillable = [ 'sdc_id_id', 'dt_id'];

    public function dayType(){
        return $this->belongsTo(DayType::class,'dt_id','dt_id');
    }

    public function dcSalesItineraryDate(){
        return $this->belongsTo(DCSalesItineraryDate::class,'sdc_id_id','sdc_id_id');
    }
}
