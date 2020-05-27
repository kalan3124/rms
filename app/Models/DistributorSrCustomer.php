<?php
namespace App\Models;

/**
 * DSR Customer allocation
 *
 * @property int $u_id
 * @property int $dc_id
 *
 * @property User $user
 * @property DistributorCustomer $distributorCustomer
 */
class DistributorSrCustomer extends Base {
    protected $table = 'distributor_sr_customer';

    protected $primaryKey = 'dsc_id';

    protected $fillable = [
        "u_id","dc_id"
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function distributorCustomer(){
        return $this->belongsTo(DistributorCustomer::class,'dc_id','dc_id');
    }

}
