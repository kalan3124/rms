<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Return note table
 * 
 * @property int $rn_id
 * @property string $rn_no
 * @property int $u_id
 * @property int $chemist_id
 * @property int $is_sheduled
 * @property string $remark
 * @property int $sr_availability
 * @property int $mr_availability
 * @property float $latitude
 * @property float $longitude
 * @property string $rn_time
 * @property float $battery_lvl
 * @property string $app_version
 * 
 * @property Collection $user
 * @property Collection $chemist
 */

 class SfaReturnNote extends Base{

    protected $table = 'sfa_return_note';

    protected $primaryKey = 'rn_id';

    protected $fillable = [
        'rn_no',
        'u_id',
        'chemist_id',
        'is_sheduled',
        'remark',
        'sr_availability',
        'mr_availability',
        'latitude',
        'longitude',
        'rn_time',
        'battery_level',
        'app_version'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }
 }