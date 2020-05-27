<?php
namespace App\Models;

class AdditionalRoutePlanArea extends Base {
    protected $table = 'additional_route_plan_areas';

    protected $primaryKey = 'arpa_id';

    protected $fillable = [
        'arp_id','twn_id','ar_id','dis_id','pv_id','sub_twn_id','rg_id'
    ];

    public function additionalRoutePlan(){
        return $this->belongsTo(AdditionalRoutePlan::class,'arp_id','arp_id');
    }

    public function area (){
        return $this->belongsTo(Area::class,'ar_id','ar_id');
    }

    public function district (){
        return $this->belongsTo(District::class,'dis_id','dis_id');
    }

    public function province (){
        return $this->belongsTo(Province::class,'pv_id','pv_id');
    }
    
    public function town(){
        return $this->belongsTo(Town::class,'twn_id','twn_id');
    }

    public function region(){
        return $this->belongsTo(Region::class,'rg_id','rg_id');
    }

    public function subTown(){
        return $this->belongsTo(SubTown::class,'sub_twn_id','sub_twn_id');
    }

    public function sub_town(){
        return $this->subTown();
    }
}