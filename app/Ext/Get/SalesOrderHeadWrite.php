<?php 
namespace App\Ext\Get;

use Illuminate\Database\Eloquent\Model;

class SalesOrderHeadWrite extends Model
{
    protected $connection = 'oracle';
    // protected $connection = 'oracle_test';
    // protected $connection = 'mysql2';

    protected $table = 'ifsapp.EXT_SFA_ORDER_HEAD_TAB';
    // protected $table = 'ext_sfa_order_head_tab';

    public $timestamps = false;

    public $incrementing=false;

    protected $fillable = [
        'cash_register_id',
        'contract',
        'sfa_order_no',
        'sfa_order_created_date',
        'sfa_order_sync_date',
        'order_date',
        'order_id',
        'customer_no',
        'currency_code',
        'wanted_delivery_date',
        'customer_po_no',
        'salesman',
        'region_code',
        'market_code',
        'district_code',
        'authorize_code',
        'bill_addr_no',
        'ship_addr_no',
        'person_id',
        'order_type',
        'status',
        'error_text'
    ];

}