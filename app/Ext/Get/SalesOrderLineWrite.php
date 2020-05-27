<?php 
namespace App\Ext\Get;

use Illuminate\Database\Eloquent\Model;

class SalesOrderLineWrite extends Model
{
    protected $connection = 'oracle';
    // protected $connection = 'oracle_test';
    // protected $connection = 'mysql2';
    
    protected $table = 'ifsapp.EXT_SFA_ORDER_LINE_TAB';
    // protected $table = 'ext_sfa_order_line_tab';

    public $timestamps = false;

    public $incrementing=false;

    protected $fillable = [
        'sfa_order_no',
        'sfa_order_line_no',
        'catalog_no',
        'quantity',
        'line_created_date',
        'status'
    ];
}