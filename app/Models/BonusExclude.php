<?php

namespace App\Models;

/**
 * Bonus Excludings
 * 
 * @property int $bnse_id Auto Increment
 * @property int $bns_id
 * @property int $bnse_bns_id
 * 
 * @property Bonus $bonus
 * @property Bonus $excludedBonus
 */
class BonusExclude extends Base {
    protected $primaryKey = 'bnse_id';

    protected $table = 'bonus_exclude';

    protected $fillable = [
        'bns_id',
        'bnse_bns_id'
    ];

    public function bonus(){
        return $this->belongsTo(Bonus::class,'bns_id','bns_id');
    }

    public function excludedBonus(){
        return $this->belongsTo(Bonus::class,'bnse_bns_id','bns_id');
    }
}