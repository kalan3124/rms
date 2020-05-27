<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Bonus model
 * 
 * @property int $bns_id
 * @property string $bns_code
 * @property string $bns_name
 * @property string $bns_start_date
 * @property string $bns_end_date
 * @property int $bns_all (1=Effecting to all distributors)
 * 
 * @property User[]|Collection $distributors
 * @property BonusProduct[]|Collection $products
 * @property BonusFreeProduct[]|Collection $freeProducts
 * @property BonusRatio[]|Collection $ratios
 * @property BonusExclude[]|Collection $excludes
 */
class Bonus extends Base {
    protected $primaryKey = 'bns_id';

    protected $table = 'bonus';

    protected $fillable = [
        'bns_name',
        'bns_code',
        'bns_start_date',
        'bns_end_date',
        'bns_all'
    ];

    public function distributors(){
        return $this->hasMany(BonusDistributor::class,'bns_id','bns_id');
    }

    public function freeProducts(){
        return $this->hasMany(BonusFreeProduct::class,'bns_id','bns_id');
    }

    public function products(){
        return $this->hasMany(BonusProduct::class,'bns_id','bns_id');
    }

    public function ratios(){
        return $this->hasMany(BonusRatio::class,'bns_id','bns_id');
    }

    public function excludes(){
        return $this->hasMany(BonusExclude::class,'bns_id','bns_id');
    }
}