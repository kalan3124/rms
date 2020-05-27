<?php
namespace App\Models;

class SfaSrItineraryDetail extends Base {
    protected $table = 'sfa_sr_itinerary_details';

    protected $primaryKey = 'sr_i_id';

    protected $fillable =['sr_i_year','sr_i_month','sr_i_date','sr_id','route_id','outlet_count','sr_mileage','bt_id'];

}