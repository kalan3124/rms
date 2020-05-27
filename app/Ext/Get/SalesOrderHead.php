<?php 
namespace App\Ext\Get;

use App\Ext\Get\HasModel\Model;

class SalesOrderHead extends Model
{
    // protected $connection = 'oracle_test';
    protected $connection = 'mysql';

    // protected $table = 'ifsapp.EXT_SFA_ORDER_HEAD_TAB';
    protected $table = 'ext_sfa_order_head_tab';

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