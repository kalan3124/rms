<?php

namespace App\Models;

class DoctorProduct extends Base
{
    protected $table = 'doctor_products';

    protected $primaryKey = 'dp_id';

    protected $fillable = [
        'product_id','doc_id'
    ];

    public function doctor (){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }
}
