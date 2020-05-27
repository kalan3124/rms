<?php 
namespace App\Models;

class DoctorPromotionDetailsByMr extends Base{

    protected $table = 'doctor_promotion_details_by_mr';

    protected $primarykey = 'dpdbmr_id';

    protected $fillable = [
        'dpbmr_id','product_id'
    ];

    public function doctor_promo_by_mr(){
        return $this->belongsTo(DoctorPromotionByMr::class,'dpbmr_id','dpbmr_id');
    }
    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}