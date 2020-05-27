<?php

namespace App\Models;

/**
 * Use this model when using YTD Achievement
 * 
 * @property int $u_id
 * @property int $product_id
 * @property int $chemist_id
 * @property int $sub_twn_id
 * @property int $mwa_year
 * @property int $mwa_month
 * @property int $mwa_day
 * @property int $mwa_sales_allocation
 * @property int $mwa_qty
 * @property float $mwa_amount
 * 
 * @property User $user
 * @property Product $product
 * @property Chemist $chemist
 * @property SubTown $subTown
 */
class MonthWiseAchievement extends Base {
    protected $table= 'month_wise_achievement';

    protected $primaryKey = 'mwa_id';

    protected $fillable = [
        'u_id',
        'product_id',
        'chemist_id',
        'sub_twn_id',
        'mwa_year',
        'mwa_month',
        'mwa_day',
        'mwa_sales_allocation',
        'mwa_qty',
        'mwa_amount'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }

    public function subTown(){
        return $this->belongsTo(SubTown::class,'sub_twn_id','sub_twn_id');
    }
}