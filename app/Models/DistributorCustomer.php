<?php

namespace App\Models;

/**
 * Distributor Customers
 *
 * @property string $dc_code
 * @property string $dc_name
 * @property string $dc_address
 * @property string $dc_image_url
 * @property float $dc_lon
 * @property float $dc_lat
 * @property int $sub_twn_id
 * @property int $price_group
 * @property int $dc_is_vat
 *
 * @property SubTown $sub_town
 * @property SalesPriceLists $sales_price_lists
 */
class DistributorCustomer extends Base
{
    protected $table = 'distributor_customer';

    protected $primaryKey = 'dc_id';

    protected $fillable = [
        "dc_code",
        "dc_name",
        "dc_address",
        "dc_image_url",
        "dc_lon",
        "dc_lat",
        "sub_twn_id",
        "price_group",
        'route_id',
        'dcc_id',
        'dcs_id',
        'dc_is_vat'
    ];

    protected $codeName = 'dc_code';

    public function sub_town(){
        return $this->belongsTo(SubTown::class,'sub_twn_id','sub_twn_id');
    }

    public function sales_price_lists(){
        return $this->belongsTo(SalesPriceLists::class,'price_group','spl_id');
    }

    public function distributor_customer_class(){
        return $this->belongsTo(DistributorCustomerClass::class,'dcc_id','dcc_id');
    }

    public function distributor_customer_segment(){
        return $this->belongsTo(DistributorCustomerSegment::class,'dcs_id','dcs_id');
    }

    public function route(){
        return $this->hasOne(Route::class,'route_id','route_id');
    }


}
