<?php 

namespace App\Models;

class DayTarget extends Base
{
    protected $table = 'sfa_daily_target';

    protected $primaryKey = 'sfa_daily_target_id';

    protected $fillable = [
        'target_day','day_target','sr_code','ar_code'
    ];
}