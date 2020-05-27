<?php

namespace App\Models;

use App\Exceptions\MediAPIException;
use Illuminate\Database\Eloquent\Collection;


class ItineraryDate extends Base
{
    protected $table = 'itinerary_date';

    protected $primaryKey = 'id_id';

    protected $fillable = ['id_date','i_id','sid_id','u_id','id_mileage','bt_id','btc_id','idc_id'];

    public function itinerary (){
        return $this->belongsTo(Itinerary::class,'i_id','i_id');
    }

    public function itineraryDayTypes(){
        return $this->hasMany(ItineraryDayType::class,'id_id','id_id');
    }

    public function standardItineraryDate(){
        return $this->belongsTo(StandardItineraryDate::class,'sid_id','sid_id');
    }

    public function changedItineraryDate(){
        return $this->belongsTo(ItineraryDateChange::class,'idc_id','idc_id');
    }

    public function additionalRoutePlan(){
        return $this->hasOne(AdditionalRoutePlan::class,'id_id','id_id');
    }

    public function joinFieldWorker(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function bataType(){
        return $this->belongsTo(BataType::class,'bt_id','bt_id');
    }

    public function bata_category(){
        return $this->belongsTo(BataCategory::class,'btc_id','btc_id');
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

        // $invoice = InvoiceHead::get();
        $itineraryQuery = Itinerary::where([
            'i_year' => date('Y',$today),
            'i_month' => date('m',$today),
        ])
        ->where(function($query)use($user){
            $query->orWhere('rep_id' , $user->getKey());
            $query->orWhere('fm_id' , $user->getKey());
        })
        ->latest();

        if($approved)
            $itineraryQuery->whereNotNull('i_aprvd_at');

        $itinerary = $itineraryQuery->first();

        if(!$itinerary)
            throw new MediAPIException("Can not find an itinerary for you or your JFW!",29);

        // Getting itinerary date for this month
        $itineraryDate = self::with($with)->with('joinFieldWorker')->where([
            'i_id' => $itinerary->getKey(),
            'id_date' => date('d',$today),             
        ])->latest()->first();

        if(!$itineraryDate)
            throw new MediAPIException("Can not find an itinerary today for you or your JFW!",30);

        if($original)
            return $itineraryDate;

        if(isset($itineraryDate->joinFieldWorker))
            return self::getTodayForUser($itineraryDate->joinFieldWorker,$with,$today,$approved);

        return $itineraryDate;
    }

    public function getFormatedDetails(){
        $bataType = null;
        $dateType = 2;
        $mileage = 0;
        $dayTypes = [];

        if(isset($this->changedItineraryDate)&&isset($this->changedItineraryDate->idc_aprvd_at)){
            $dateType = 7;
            $mileage = $this->changedItineraryDate->idc_mileage;
            $bataType = isset($this->changedItineraryDate->bataType)?$this->changedItineraryDate->bataType:$bataType;
            $areas = $this->changedItineraryDate->areas;

        }else if(isset($this->standardItineraryDate)){
            $dateType = 3;
            $mileage = $this->standardItineraryDate->sid_mileage;
            $bataType = isset($this->standardItineraryDate->bataType)?$this->standardItineraryDate->bataType:$bataType;
            $areas = clone $this->standardItineraryDate->areas;
        }else if(isset($this->additionalRoutePlan)){
            $dateType=4;
            $mileage = $this->additionalRoutePlan->arp_mileage;
            $bataType = isset($this->additionalRoutePlan->bataType)?$this->additionalRoutePlan->bataType:$bataType;
            $areas = $this->additionalRoutePlan->areas;

        } else if(isset($this->joinFieldWorker)){
            $dateType = 5;
            $mileage = $this->id_mileage;
            $bataType = isset($this->bataType)?$this->bataType:$bataType;
        }  else {
            $dateType = 0;
            $mileage = $this->id_mileage;
            $bataType = isset($this->bataType)?$this->bataType:$bataType;
        }

        $isWorkingDay = false;
        $isFieldWorkingDay = false;

        if(isset($this->itineraryDayTypes)){
            /** @var Collection $itineraryDayTypes */
            $itineraryDayTypes = $this->itineraryDayTypes->map(function( ItineraryDayType $itineraryDayType ){
                if($itineraryDayType->dayType){
                    return $itineraryDayType->dayType;
                }

                return null;
            });

            $isWorkingDay = !!$itineraryDayTypes->where('dt_is_working',1)->count();
            $isFieldWorkingDay = !!$itineraryDayTypes->where('dt_field_work_day',1)->count();
            
            $dayTypes = $itineraryDayTypes->filter(function($dayType){return !!$dayType;})->values()->all();
        }

        if(!isset($areas)){
            $areas = collect([]);
        }


        $areas->transform(function($area){
            if(!isset($area->subTown))
                return null;

            return $area->subTown;
        });


        $areas = $areas->filter(function($subTown){return !!$subTown;});

        $details = new ItineraryDateDetails();

        $details->setBataType($bataType);
        $details->setMileage($mileage);
        $details->setSubTowns($areas->values());
        $details->setDateType($dateType);
        $details->setDayTypes($dayTypes);
        $details->setFieldWorkingDay($isFieldWorkingDay);
        $details->setWorkingDay($isWorkingDay);

        return $details;
    }
}
