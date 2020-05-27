<?php
namespace App\Models;

/**
 * User Team Allocation
 * 
 * @property int $u_id
 * @property int $tm_id
 * 
 * @property User $user
 * @property Team $team
 */
class UserTeam extends Base {
    protected $table = 'user_team';

    protected $primaryKey = 'ut_id';

    protected $fillable = [
        'u_id','tm_id'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function team(){
        return $this->belongsTo(Team::class,'tm_id','tm_id');
    }
}