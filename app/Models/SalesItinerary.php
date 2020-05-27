<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Sales itinerary
 * 
 * @property int $s_i_id
 * @property int $u_id
 * @property int $s_i_year
 * @property int $s_i_month
 * @property string $s_i_aprvd_at
 * @property int $s_aprvd_u_id
 * 
 * @property User $user
 * @property User $approver
 * @property SalesItineraryDate[]|Collection $salesItineraryDates
 */
class SalesItinerary extends Base {
    protected $table = 'sfa_itinerary';

    protected $primaryKey = 's_i_id';

    protected $fillable =['u_id','s_i_year','s_i_month','s_i_aprvd_at','s_aprvd_u_id'];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
    
    public function approver(){
        return $this->belongsTo(User::class,'s_aprvd_u_id','id');
    }

    public function salesItineraryDates(){
        return $this->hasMany(SalesItineraryDate::class,'s_i_id','s_i_id');
    }
}