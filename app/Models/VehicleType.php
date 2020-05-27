<?php
namespace  App\Models;

class VehicleType extends Base{
    protected $table = 'vehicle_types';

    protected $primaryKey = 'vht_id';

    protected $codeName = 'vht_code';

    protected $fillable = [
        'vht_name'
    ];
}