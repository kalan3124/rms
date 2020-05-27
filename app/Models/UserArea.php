<?php

namespace App\Models;

class UserArea extends Base
{
    protected $table = 'user_areas';

    protected $primaryKey = 'ua_id';

    protected $fillable = [
        'u_id','ar_id','dis_id','pv_id','twn_id','rg_id','sub_twn_id'
    ];

    public function user (){
        return $this->belongsTo(User::class,'u_id'.'u_id');
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
}
