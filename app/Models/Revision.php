<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model {
    protected $table = 'revisions';

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}