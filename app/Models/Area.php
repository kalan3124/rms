<?php

namespace App\Models;

class Area extends Base
{
    protected $table = 'area';

    protected $primaryKey = 'ar_id';

    protected $fillable = [
        'ar_name','ar_short_name','ar_code','dis_id'
    ];

    protected $codeName = 'ar_code';

    public function region(){
        return $this->belongsTo(Region::class,'rg_id','rg_id');
    }

    public function towns(){
        return $this->hasMany(Town::class,'twn_id');
    }
}
