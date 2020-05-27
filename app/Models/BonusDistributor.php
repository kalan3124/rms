<?php
namespace App\Models;

/**
 * Bonus Distributor
 * 
 * @property int $bnsd_id
 * @property int $bns_id
 * @property int $dis_id
 * 
 * @property Bonus $bonus
 * @property User $distributor
 */
class BonusDistributor extends Base {
    protected $table = 'bonus_distributor';

    protected $primaryKey = 'bnsd_id';

    protected $fillable = [
        'dis_id',
        'bns_id'
    ];

    public function bonus(){
        return $this->belongsTo(Bonus::class,'bns_id','bns_id');
    }

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }
}