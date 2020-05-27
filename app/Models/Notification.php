<?php
namespace App\Models;

class Notification extends Base {
    protected $table="notifications";

    protected $primaryKey = "n_id";

    protected $fillable=['n_title','n_content','n_seen','u_id','n_created_u_id','n_type','n_ref_id'];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function creater(){
        return $this->belongsTo(User::class,'n_created_u_id','id');
    }
}