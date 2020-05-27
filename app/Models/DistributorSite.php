<?php
namespace App\Models;

/**
 * Distributor's sites
 * 
 * @param int $site_allo_id Auto increment id
 * @param int $site_id
 * @param int $dis_id
 * 
 * @param Site $site
 * @param Distributor $distributor
 */
class DistributorSite extends Base {

    protected $table = 'site_allocation';

    protected $primaryKey = 'site_allo_id';

    protected $fillable = [
        'site_id','dis_id'
    ];

    public function site(){
        return $this->hasOne(Site::class,'site_id','site_id');
    }

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }
}