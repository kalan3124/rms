<?php

namespace App\Models;

/**
 * Android app model
 * 
 * @property int $aa_v_type 1=FFA , 2=SFA , 3= SFA-DIST
 * @property string $aa_v_name
 * @property string $aa_description
 * @property string $aa_start_time
 * @property string $aa_url
 */
class AndroidApp extends Base
{
    protected $table = 'android_app';

    protected $primaryKey = 'aa_id';

    protected $fillable = [
        'aa_v_type',
        'aa_v_name',
        'aa_description',
        'aa_start_time',
        'aa_url'
    ];
}
