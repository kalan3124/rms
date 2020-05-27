<?php
namespace App\Models;

class AdditionalRoutePlan extends Base{
    protected $table = 'additional_route_plans';

    protected $primaryKey='arp_id';

    protected $fillable=[
        'id_id',
        'arp_description',
        'arp_mileage',
        'bt_id'
    ];

    public function itineraryDate(){
        return $this->belongsTo(ItineraryDate::class,'id_id','id_id');
    }

    public function bataType(){
        return $this->belongsTo(BataType::class,'bt_id','bt_id');
    }

    public function areas(){
        return $this->hasMany(AdditionalRoutePlanArea::class,'arp_id','arp_id');
    }

    public function additionalRoutePlanAreas(){
        return $this->areas();
    }
    
}