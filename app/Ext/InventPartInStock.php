<?php
namespace App\Ext;

use App\Models\Base;

class InventPartInStock extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'ext_invent_part_in_stock_uiv';

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
        'last_updated_on'
    ];
}
