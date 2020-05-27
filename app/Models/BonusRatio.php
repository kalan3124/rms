<?php
namespace App\Models;

/**
 * Bonus Ratios
 * 
 * @property int $bns_id
 * @property int $bnsr_min
 * @property int $bnsr_max
 * @property int $bnsr_purchase
 * @property int $bnsr_free
 */
class BonusRatio extends Base {
    protected $table = 'bonus_ratio';

    protected $primaryKey = 'bnsr_id';

    protected $fillable = [
        'bns_id',
        'bnsr_min',
        'bnsr_max',
        'bnsr_purchase',
        'bnsr_free'
    ];

}