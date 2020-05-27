<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Route
 *
 * @property string $route_code
 * @property string $route_name
 * @property int $ar_id
 * @property int $seq_no
 * @property int $route_type 0 = Sales Rep | 1 = Distributor | 2 = dc
 * @property int $route_schedule
 *
 * @property Area $area
 * @property RouteChemist[]|Collection $routeChemists
 */
class Route extends Base
{
    protected $table = 'sfa_route';

    protected $primaryKey = 'route_id';

    protected $fillable = [
        'route_code','route_name','ar_id','seq_no','route_type' , 'route_schedule'
    ];

    public function routeChemists(){
        return $this->hasMany(RouteChemist::class,'route_id','route_id');
    }

    public function area(){
        return $this->belongsTo(Area::class,'ar_id','ar_id');
    }
}
