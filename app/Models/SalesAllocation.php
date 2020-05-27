<?php

namespace App\Models;

class SalesAllocation extends Base {
    protected $table = 'sales_allocation';

    protected $primaryKey = 'sa_id';


    protected $fillable = [
        'tm_id',
        'sa_ref_type',
        'sa_ref_id',
        'sa_ref_mode',
        'sam_id'
    ];

    public function team(){
        return $this->belongsTo(Team::class,'tm_id','tm_id');
    }

    public function town(){
        return $this->belongsTo(SubTown::class,'sa_ref_id','sub_twn_id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'sa_ref_id','chemist_id');
    }

    public function product(){
        return $this->belongsTo(Product::class,'sa_ref_id','product_id');
    }

    public function invoice(){
        return $this->belongsTo(Invoice::class,'sa_ref_id','inv_head_id');
    }

    public function salesAllocationMain(){
        return $this->belongsTo(SalesAllocationMain::class,'sam_id','sam_id');
    }
}