<?php

namespace App\Models;

class TmpSalesAllocation extends Base {
    protected $table = 'tmp_sales_allocation';

    protected $primaryKey = 'tsa_id';

    protected $fillable = [
        'sam_id',
        'chemist_id',
        'product_id',
        'u_id',
        'tsa_percent'
    ];

    public function salesAllocation(){
        return $this->belongsTo(SalesAllocationMain::class,'sam_id','sam_id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'product_id','product_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}