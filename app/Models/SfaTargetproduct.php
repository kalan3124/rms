<?php 
namespace App\Models;

class SfaTargetProduct extends Base{

    protected $table = 'sfa_target_products';

    protected $primaryKey = 'sfa_tp_id';

    protected $fillable = [
        'sfa_trg_id',
        'product_id',
        'stp_qty',
        'budget_price',
        'stp_amount'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}