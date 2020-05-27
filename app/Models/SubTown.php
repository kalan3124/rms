<?php

namespace App\Models;

class SubTown extends Base
{
    protected $table = 'sub_town';

    protected $primaryKey = 'sub_twn_id';

    protected $fillable = [
        'sub_twn_code','sub_twn_name','twn_id'
    ];
    
    protected $codeName = 'sub_twn_code';

    public function town(){
        return $this->belongsTo(Town::class,'twn_id','twn_id');
    }

    public function chemist(){
        return $this->hasMany(Chemist::class,'sub_twn_id','sub_twn_id');
    }
}
