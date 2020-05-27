<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Stock Adjustment Parent Model
 * 
 * @property int $stk_adj_id Auto Increment
 * @property string $stk_adj_no
 * @property int $dis_id
 * @property string $stk_adj_date
 * 
 * @property User $distributor
 * @property StockAdjusmentProduct[]|Collection $lines
 */
class StockAdjusment extends Base
{
    protected $table='stock_adjusment';

    protected $primaryKey = 'stk_adj_id';

    protected $codeName = 'stk_adj_no';

    protected $fillable = [
        'stk_adj_no','dis_id','stk_adj_date','ajust_u_id'
    ];

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }

    public function lines(){
        return $this->belongsTo(StockAdjusmentProduct::class,'stk_adj_id','stk_adj_id');
    }
}
