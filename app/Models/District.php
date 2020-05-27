<?php

namespace App\Models;

class District extends Base
{
    protected $table="district";

    protected $primaryKey = 'dis_id';

    protected $fillable=[
        'dis_name','dis_short_name','dis_code','pv_id'
    ];

    protected $codeName = 'dis_code';

    public function province(){
        return $this->belongsTo(Province::class,'pv_id','pv_id');
    }

    public function areas(){
        return $this->hasMany(Area::class,'ar_id');
    }
}
