<?php
namespace App\Models;

/**
 * Distributor sites
 * 
 * @property int $site_id
 * @property string $site_name
 * @property string $site_code
 */
class Site extends Base {
    protected $table = 'site';

    protected $primaryKey = 'site_id';

    protected $fillable = [
        'site_name',
        'site_code'
    ];
}