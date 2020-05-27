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
 * @property SalesItineraryDateDayType[] $salesItineraryDateDayTypes
 * @property SalesItineraryDateJFW[] $jointFieldWorkers
 * @property BataType $bataType
 * @property Route $route
 * @property SalesItinerary $salesItinerary
 */
class SalesItineraryDate extends Base {
    protected $table = 'sfa_itinerary_date';

    protected $primaryKey = 's_id_id';

    protected $fillable = ['s_id_date','bt_id','s_id_mileage','s_i_id','route_id','s_id_type','day_target'];

    public function bataType(){
        return $this->belongsTo(BataType::class,'bt_id','bt_id');
    }

    public function salesItinerary(){
        return $this->belongsTo(SalesItinerary::class,'s_i_id','s_i_id');
    }

    public function route(){
        return $this->belongsTo(Route::class,'route_id','route_id');
    }

    public function salesItineraryDateDayTypes(){
        return $this->hasMany(SalesItineraryDateDayType::class,'s_id_id','s_id_id');
    }

    public function jointFieldWorkers(){
        return $this->hasMany(SalesItineraryDateJFW::class,'s_id_id','s_id_id');
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

        $itineraryQuery = SalesItinerary::where([
            's_i_year' => date('Y',$today),
            's_i_month' => date('m',$today),
            'u_id' => $user->getKey()
        ])
        ->latest();

        if($approved)
            $itineraryQuery->whereNotNull('s_i_aprvd_at');

        $itinerary = $itineraryQuery->first();

        if(!$itinerary)
            throw new MediAPIException("Can not find an itinerary for you or your JFW!z",29);

        // Getting itinerary date for this month
        $itineraryDate = self::with($with)->with('jointFieldWorkers')->where([
            's_i_id' => $itinerary->getKey(),
            's_id_date' => date('d',$today),             
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