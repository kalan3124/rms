<?php

namespace App\Models;

class Chemist extends Base
{
    protected $table = "chemist";  

    protected $primaryKey = 'chemist_id';

    protected $fillable=[
        'chemist_name','chemist_code','chemist_address','telephone','credit_amount','sub_twn_id','chemist_class_id','chemist_type_id','chemist_mkd_id','route_id'
    ];

    protected $codeName = 'chemist_code';

    public function chemist_class(){
        return $this->belongsTo(ChemistClass::class,'chemist_class_id','chemist_class_id');
    }

    public function chemist_types(){
        return $this->belongsTo(ChemistTypes::class,'chemist_type_id','chemist_type_id');
    }

    public function chemist_market_description(){
        return $this->belongsTo(ChemistMarketDescription::class,'chemist_mkd_id','chemist_mkd_id');
    }

    public function sub_town (){
        return $this->belongsTo(SubTown::class,'sub_twn_id','sub_twn_id');
    }

    public function route(){
        return $this->belongsTo(Route::class,'route_id','route_id');
    }

}
