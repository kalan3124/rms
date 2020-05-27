<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Stock Write Off Model
 * 
 * @property int $wo_id Auto Increment ID
 * @property int $dis_id
 * @property string $wo_date
 * @property string $wo_no
 * 
 * @property User $distributor
 * @property Collection|StockWriteOffProduct[] $lines
 */
class StockWriteOff extends Base
{
    protected $table='write_off';

    protected $primaryKey = 'wo_id';

    protected $codeName = 'wo_no';

    protected $fillable = [
        'wo_no','dis_id','wo_date','write_off_u_id'
    ];

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }

    public function lines(){
        return $this->hasMany(StockWriteOffProduct::class,'wo_id','wo_id');
    }
}
