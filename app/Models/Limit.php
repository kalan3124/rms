<?php
namespace App\Models;
/**
 * Model for all limits
 * 
 * @property int $lmt_main_type
 *  
 *  1   =   Expenses
 * 
 * @property int $lmt_sub_type
 * 
 *  1   =   Private Mileage
 *  2   =   Parking Expenses
 *  3   =   Additional Mileage
 * 
 * @property int $lmt_frequency
 * 
 *  1   =   Daily
 *  2   =   Monthly
 *  3   =   Yearly
 * 
 * @property float $lmt_min_amount First amount of limitation
 * @property float $lmt_max_amount Maximum amount of limitation.
 *  Leave it if limitation has only one value
 * 
 * @property string $lmt_start_at Starting date of the limitation.
 *  Leave it if limitation hasn't a date range
 * @property string $lmt_end_at Starting date of the limitation.
 *  Leave it if limitation hasn't a date range
 * 
 * @property int $lmt_ref_id Reference record id.
 */
class Limit extends Base {
    protected $table ="limit";

    protected $primaryKey = 'lmt_id';

    protected $fillable = ['lmt_main_type','lmt_sub_type','lmt_frequency','lmt_min_amount','lmt_max_amount','lmt_start_at','lmt_end_at','lmt_ref_id'];
}