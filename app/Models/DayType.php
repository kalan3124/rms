<?php

namespace App\Models;

class DayType extends Base
{
    protected $table = 'day_type';

    protected $primaryKey = 'dt_id';

    protected $fillable = ['dt_name','dt_is_working','dt_color','dt_code',"dt_bata_enabled", "dt_mileage_enabled",'dt_field_work_day'];

    protected $codeName = 'dt_code';
}
