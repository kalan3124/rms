<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Sales order table
 *
 * @property string $order_no
 * @property int $u_id
 * @property string $order_date
 * @property int $order_mode
 * @property int $order_type
 * @property int $dc_id
 * @property float $latitude
 * @property float $longitude
 * @property float $battery_lvl
 * @property string $app_version
 * @property int $contract
 * @property string $integrated_at
 * @property int $ar_id
 * @property float $sales_order_amt
 * @property int $dis_id
 * @property int $is_invoiced
 * @property float $discount
 * @property string $remark
 *
 * @property User $user
 * @property DistributorCustomer $distributorCustomer
 * @property Collection|DistributorSalesOrderProduct[] $salesOrderProducts
 * @property Collection|DsitributorSalesOrderBonusProduct[] $salesOrderBonusProducts
 * @property Area $area
 */
class DistributorSalesOrder extends Base{

    protected $table = 'distributor_sales_order';

    protected $primaryKey = 'dist_order_id';

    protected $fillable =[
        'order_no',
        'u_id',
        'order_date',
        'order_mode',
        'order_type',
        'dc_id',
        'latitude',
        'longitude',
        'battery_lvl',
        'app_version',
        'contract',
        'integrated_at',
        'ar_id',
        'sales_order_amt',
        'dis_id',
        'is_invoiced',
        'discount',
        'remark'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function distributorCustomer(){
        return $this->belongsTo(DistributorCustomer::class,'dc_id','dc_id');
    }

    public function salesOrderProducts(){
        return $this->hasMany(DistributorSalesOrderProduct::class,'dist_order_id','dist_order_id');
    }

    public function salesOrderBonusProducts(){
        return $this->hasMany(DistributorSalesOrderBonusProduct::class,'dist_order_id','dist_order_id');
    }

    public function area(){
        return $this->belongsTo(Area::class,'ar_id','ar_id');
    }
}
