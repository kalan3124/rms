<?php 
namespace App\Models;

class InventPartInStock extends Base
{
    protected $table = 'invent_part_in_stock';

    protected $primaryKey = 'inpts_id';

    protected $fillable = [
        'rn',
        'contract',
        'part_no',
        'location_no',
        'lot_batch_no',
        'serial_no',
        'w_d_r_no',
        'expiration_date',
        'last_activity_date',
        'last_count_date',
        'location_type',
        'qty_in_transit',
        'qty_onhand',
        'qty_reserved',
        'available_qty',
        'receipt_date',
        'availability_control_id',
        'create_date',
        'last_updated_on',
        'product_id'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}