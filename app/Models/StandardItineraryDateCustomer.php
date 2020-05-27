<?php

namespace App\Models;

/**
 * Customers for the standard itinerary dates
 * 
 * @property int $sidc_id
 * @property int $sid_id
 * @property int $chemist_id
 * @property int $doc_id
 * @property int $hos_stf_id
 * 
 * @property Chemist $chemist
 * @property Doctor $doctor
 * @property OtherHospitalStaff $otherHospitalStaff
 * @property StandardItineraryDate $standardItineraryDate
 */
class StandardItineraryDateCustomer extends Base
{
    protected $table = 'standard_itinerary_date_customer';

    protected $primaryKey = 'sidc_id';

    protected $fillable = [
        'sid_id','chemist_id','doc_id','hos_stf_id'
    ];

    public function standardItineraryDate (){
        return $this->belongsTo(StandardItineraryDate::class,'sid_id','sid_id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }

    public function doctor(){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }

    public function otherHospitalStaff(){
        return $this->belongsTo(OtherHospitalStaff::class,'hos_stf_id','hos_stf_id');
    }
}
