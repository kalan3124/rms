<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Sales order table
 *
 * @property int $order_id
 * @property string $order_no
 * @property int $u_id
 * @property string $order_date
 * @property int $order_mode
 * @property int $order_type
 * @property int $chemist_id
 * @property float $latitude
 * @property float $longitude
 * @property float $battery_lvl
 * @property string $app_version
 *
 * @property User $user
 * @property Chemist $chemist
 * @property Collection $salesOrderProducts
 */
class SfaSalesOrder extends Base{

    protected $table = 'sfa_sales_order';

    protected $primaryKey = 'order_id';

    protected $fillable =[
        'order_no',
        'u_id',
        'order_date',
        'order_mode',
        'order_type',
        'chemist_id',
        'latitude',
        'longitude',
        'battery_lvl',
        'app_version',
        'contract',
        'integrated_at',
        'ar_id',
        'sub_twn_id',
        'sales_order_amt'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }

    public function salesOrderProducts(){
        return $this->hasMany(SfaSalesOrderProduct::class,'order_id','order_id');
    }

    public function area(){
        return $this->belongsTo(Area::class,'ar_id','ar_id');
    }
}
