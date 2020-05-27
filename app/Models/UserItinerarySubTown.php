<?php

namespace App\Models;

use App\Models\User;

class UserItinerarySubTown extends Base
{
    protected $table = 'tmp_user_itinerary_sub_town';

    protected $primaryKey = 'uist_id';

    protected $fillable = ['u_id','sub_twn_id','arp_id','sid_id','i_id','id_id','uist_year','uist_month','uist_date','uist_approved','uist_jfw_id','idc_id'];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function subTown(){
        return $this->belongsTo(SubTown::class,'sub_twn_id','sub_twn_id');
    }

    public function standardItineraryDate(){
        return $this->belongsTo(StandardItineraryDate::class,'sid_id','sid_id');
    }

    public function additionalRoutePlan(){
        return $this->belongsTo(AdditionalRoutePlan::class,'arp_id','arp_id');
    }

    public function itineraryDate(){
        return $this->belongsTo(ItineraryDate::class,'id_id','id_id');
    }

    public function changedItinerayDate(){
        return $this->belongsTo(ItineraryDateChange::class,'idc_id','idc_id');
    }

    public function itinerary(){
        return $this->belongsTo(Itinerary::class,'i_id','i_id');
    }

}
