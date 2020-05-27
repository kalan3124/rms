<?php

namespace App\Models;

class TeamUser extends Base
{
    protected $table = 'team_users';

    protected $primaryKey = 'tmu_id';

    protected $fillable = [
        'tm_id','u_id'
    ];

    public function team(){
        return $this->belongsTo(Team::class,'tm_id','tm_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function teamUserProducts(){
        return $this->hasMany(TeamUserProduct::class,'tmu_id','tmu_id');
    }
    
}
