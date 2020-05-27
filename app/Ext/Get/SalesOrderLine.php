<?php 
namespace App\Ext\Get;

use App\Ext\Get\HasModel\Model;

class SalesOrderLine extends Model
{
    protected $connection = 'mysql2';
    
    protected $table = 'ifsapp.EXT_SFA_ORDER_LINE_TAB';

    protected $fillable = [
        'sfa_order_no',
        'sfa_order_line_no',
        'catalog_no',
        'quantity',
        'line_created_date',
        'status'
    ];
}