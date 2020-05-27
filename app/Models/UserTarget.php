<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Targets for Medical Representatives
 * 
 * There are more than one targets for a month. Always get latest target.
 * 
 * @property int $ut_id Auto Increment Id
 * @property int $u_id MR User Id
 * @property float $ut_value Amount Target
 * @property int $ut_qty Qty Target
 * @property int $ut_month
 * @property int $ut_year
 * 
 * @property User $user
 * @property UserProductTarget[]|Collection $userProductTargets
 */
class UserTarget extends Base
{
    protected $table = 'user_target';

    protected $primaryKey = 'ut_id';

    protected $fillable = ['u_id','ut_value','ut_qty','ut_month','ut_year'];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function userProductTargets(){
        return $this->hasMany(UserProductTarget::class,'ut_id','ut_id');
    }
}
