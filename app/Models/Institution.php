<?php

namespace App\Models;

class Institution extends Base
{
    protected $table = 'institutions';

    protected $primaryKey = 'ins_id';

    protected $fillable = [
        'ins_name','ins_short_name','ins_code','ins_address','ins_cat_id','sub_twn_id'
    ];

    protected $codeName = 'ins_code';

    public function institution_category(){
       return $this->belongsTo(InstitutionCategory::class,'ins_cat_id','ins_cat_id');
    }

    // public function town(){
    //     return $this->belongsTo(Town::class,'twn_id','twn_id');
    // }

    public function sub_town(){
        return $this->belongsTo(SubTown::class,'sub_twn_id','sub_twn_id');
    }
}
