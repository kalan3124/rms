<?php

namespace App\Models;

class Town extends Base
{
    protected $table='town';

    protected $primaryKey = 'twn_id';

    protected $codeName = 'twn_code';

    protected $fillable = [
        'twn_name','twn_short_name','twn_code','ar_id'
    ];

    public function area(){
        return $this->belongsTo(Area::class,'ar_id','ar_id');
    }

    public function sub_town(){
        return $this->hasMany(SubTown::class,'twn_id','twn_id');
    }
}
