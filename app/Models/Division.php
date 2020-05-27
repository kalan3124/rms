<?php

namespace App\Models;

class Division extends Base
{
    protected $table="division";

    protected $primaryKey = 'divi_id';

    protected $fillable=[
        'divi_name','divi_short_name'
    ];

    protected $codeName = 'divi_short_name';

    public function users(){
        return $this->hasMany(User::class,'divi_id','divi_id');
    }
}
