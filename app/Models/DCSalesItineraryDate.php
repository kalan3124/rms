<?php
namespace App\Models;

use App\Exceptions\MediAPIException;

/**
 * Sales Itinerary Date
 *
 * @property int $s_id_id
 * @property int $s_id_date
 * @property float $s_id_mileage
 * @property int $s_i_id
 * @property int $route_id
 * @property int $s_id_type
 * @property int $bt_id
 *
 * @property DCSalesItineraryDateDayType[] $salesItineraryDateDayTypes
 * @property DCSalesItineraryDateJFW[] $jointFieldWorkers
 * @property BataType $bataType
 * @property Route $route
 * @property DCSalesItinerary $salesItinerary
 */
class DCSalesItineraryDate extends Base {
    protected $table = 'sdc_itinerary_date';

    protected $primaryKey = 'sdc_id_id';

    protected $fillable = ['sdc_id_date','bt_id','sdc_id_mileage','sdc_i_id','route_id','sdc_id_type','day_target'];

    public function bataType(){
        return $this->belongsTo(BataType::class,'bt_id','bt_id');
    }

    public function dcSalesItinerary(){
        return $this->belongsTo(DCSalesItinerary::class,'sdc_i_id','sdc_i_id');
    }

    public function route(){
        return $this->belongsTo(Route::class,'route_id','route_id');
    }

    public function dcSalesItineraryDateDayTypes(){
        return $this->hasMany(DCSalesItineraryDateDayType::class,'sdc_id_id','sdc_id_id');
    }

    public function jointFieldWorkers(){
        return $this->hasMany(DCSalesItineraryDateJFW::class,'sdc_id_id','sdc_id_id');
    }

    /**
     * Return today itinerary date
     *
     * @param \App\Models\User $user
     * @param array $with relationships
     * @param int $today default value is time()
     * @param bool $original get original value
     * @param bool $approved getting only approved itinerary or not
     * @return self
     */

    public static function getTodayForUser($user,$with=[],$today=null,$original=false,$approved=true){
        if(!$today) $today= time();

        $itineraryQuery = DCSalesItinerary::where([
            'sdc_i_year' => date('Y',$today),
            'sdc_i_month' => date('m',$today),
            'u_id' => $user->getKey()
        ])
        ->latest();

        if($approved)
            $itineraryQuery->whereNotNull('sdc_i_aprvd_at');

        $itinerary = $itineraryQuery->first();

        if(!$itinerary)
            throw new MediAPIException("Can not find an itinerary for you or your JFW!z",29);

        // Getting itinerary date for this month
        $itineraryDate = self::with($with)->with('jointFieldWorkers')->where([
            'sdc_i_id' => $itinerary->getKey(),
            'sdc_id_date' => date('d',$today),
        ])->latest()->first();

        if(!$itineraryDate)
            throw new MediAPIException("Can not find an itinerary today for you or your JFW!",30);

        if($original)
            return $itineraryDate;

        if(isset($itineraryDate->joinFieldWorker))
        return self::getTodayForUser($itineraryDate->joinFieldWorker,$with,$today,$approved);

        return $itineraryDate;
    }

}
