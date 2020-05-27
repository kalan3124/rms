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
class DCSalesItinerary extends Base {
    protected $table = 'sdc_itinerary';

    protected $primaryKey = 'sdc_i_id';

    protected $fillable =['u_id','sdc_i_year','sdc_i_month','sdc_i_aprvd_at','sdc_aprvd_u_id'];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function approver(){
        return $this->belongsTo(User::class,'sdc_aprvd_u_id','id');
    }

    public function dcSalesItineraryDates(){
        return $this->hasMany(DCSalesItineraryDate::class,'sdc_i_id','sdc_i_id'); //f key , l key
    }
}
