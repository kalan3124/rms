<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Medical Teams
 * 
 * @property int $tm_id Auto Increment
 * @property string $tm_code
 * @property string $tm_name
 * @property int $fm_id Field Manager Id (User Id)
 * @property int $hod_id Head Of Department Id (User Id)
 * @property string $tm_exp_block_date Expenses blocking date
 * @property int $divi_id Division Id
 * @property float $tm_mileage_limit
 * 
 * @property Collection|TeamUser[] $teamUsers
 * @property User $user Field Manager
 * @property Collection|TeamProduct[] $teamProducts
 * @property User $head_of_department Head Of Department
 * @property Division $division
 * 
 */
class Team extends Base
{
    protected $table = 'teams';

    protected $primaryKey = 'tm_id';

    protected $fillable = [
        'tm_code','tm_name','fm_id','hod_id','tm_exp_block_date','divi_id','tm_mileage_limit'
    ];

    protected $codeName = 'tm_code';

    public function teamUsers(){
        return $this->hasmany(TeamUser::class,'tm_id','tm_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'fm_id','id');
    }

    public function teamProducts(){
        return $this->hasMany(TeamProduct::class,'tm_id','tm_id');
    }

    public function head_of_department(){
        return $this->belongsTo(User::class,'hod_id','id');
    }

    public function division(){
        return $this->belongsTo(Division::class,'divi_id','divi_id');
    }

}
