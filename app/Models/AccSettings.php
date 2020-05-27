<?php
namespace App\Models;

class AccSettings extends Base{
    protected $table = 'acc_settings';

    protected $primaryKey='st_id';

    protected $fillable=[
        'st_type',
        'duration'
    ];
}
