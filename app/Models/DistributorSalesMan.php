<?php 
namespace App\Models;

/**
 * Distributor sales man
 * 
 * @property int $dis_salesman_id
 * @property int $dis_id
 * @property int $sr_id
 * 
 * @property User $distributor
 * @property User $distributorSalesRep
 */
class DistributorSalesMan extends Base {

    protected $table = 'distributor_salesmans';

    protected $primaryKey = 'dis_salesman_id';

    protected $fillable = [
        'dis_id','sr_id'
    ];

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }

    public function distributorSalesRep(){
        return $this->belongsTo(User::class,'sr_id','id');
    }
}