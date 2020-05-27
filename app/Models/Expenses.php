<?php

namespace App\Models;

class Expenses extends Base
{
    protected $table = 'expenses';
    
    protected $primaryKey = 'exp_id';

    protected $fillable = [
        'u_id','rsn_id','exp_amt','exp_remark','image_url','exp_date','app_version','vhtr_rate'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function reason(){
        return $this->belongsTo(Reason::class,'rsn_id','rsn_id');
    }
}
