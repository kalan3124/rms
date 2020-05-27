<?php

namespace App\Models;

class SalesAllocationMain extends Base {
    protected $table = 'sales_allocation_main';

    protected $primaryKey = 'sam_id';

    protected $fillable = [
        'tm_id'
    ];

    public function team(){
        return $this->belongsTo(Team::class,'tm_id','tm_id');
    }
}