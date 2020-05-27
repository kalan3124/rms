<?php

namespace App\Models;

class Region extends Base
{
    protected $table = 'region';

    protected $primaryKey = 'rg_id';

    protected $fillable = [
        'rg_code','rg_name','dis_id'
    ];

    protected $codeName = 'rg_code';

    public function district(){
        return $this->belongsTo(District::class,'dis_id','dis_id');
    }
}
