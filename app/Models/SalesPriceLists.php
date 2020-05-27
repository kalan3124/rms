<?php 
namespace App\Models;

class SalesPriceLists extends Base{

    protected $table = 'sales_price_list';

    protected $primaryKey = 'spl_id';

    protected $codeName = 'price_list_no';

    protected $fillable = [
        'price_list_no',
        'description',
        'sales_price_group_id',
        'currency_code',
        'catalog_no',
        'min_quantity',
        'valid_from_date',
        'base_price_site',
        'base_price',
        'base_price_incl_tax',
        'percentage_offset',
        'amount_offset',
        'sales_price',
        'sales_prices_incl_tax',
        'last_updated_on',
        'discount',
        'discount_type',
        'price_break_template_id',
        'sales_price_type',
        'state',
        'product_id'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}