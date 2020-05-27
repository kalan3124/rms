<?php
namespace App\Models;

/**
 * DSR Distributor Allocations
 * 
 * @property int $dsr_id
 * @property int $dis_id
 * @property int $sr_id
 * 
 * @property User $distributor
 * @property User $distributorSalesRep
 */
class DistributorSalesRep extends Base {

    protected $table = 'distributor_sales_rep';

    protected $primaryKey = 'dsr_id';

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