<?php

namespace App\Models;

class TeamMemberPercentage extends Base {
    protected $table = 'member_percentage';

    protected $primaryKey = 'mp_id';

    protected $fillable = [
        'u_id',
        'mp_percent',
        'sam_id'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function salesAllocationMain(){
        return $this->belongsTo(SalesAllocationMain::class,'sam_id','sam_id');
    }
}