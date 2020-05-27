<?php

namespace App\Models;

class TeamUserProduct extends Base
{
    protected $table = 'team_user_products';

    protected $primaryKey = 'tmup_id';

    protected $fillable = [
        'tmu_id','tmp_id'
    ];

    public function teamUser(){
        return $this->belongsTo(TeamUser::class,'tmu_id','tmu_id');
    }

    public function teamProduct(){
        return $this->belongsTo(TeamProduct::class,'tmp_id','tmp_id');
    }
}
