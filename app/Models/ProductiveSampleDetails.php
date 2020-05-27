<?php

namespace App\Models;

class ProductiveSampleDetails extends Base
{
    protected $table = 'productive_sample_details';

    protected $primaryKey = 'pro_smpd_id';

    protected $fillable = [
        'pro_visit_id',
        'product_id',
        'sampling_reason_id',
        'detailing_reason_id',
        'promotion_reason_id',
        'qty',
        'remark'
    ];

    public function productive_visit(){
        return $this->belongsTo(ProductiveVisit::class,'pro_visit_id','pro_visit_id');
    }
    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
    public function sampling(){
        return $this->belongsTo(Reason::class,'sampling_reason_id','rsn_id');
    }
    public function detailing(){
        return $this->belongsTo(Reason::class,'detailing_reason_id','rsn_id');
    }
    public function promotion(){
        return $this->belongsTo(Reason::class,'promotion_reason_id','rsn_id');
    }
}
