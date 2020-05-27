<?php
namespace App\Models;

class InvoiceAllocation extends Base {
    protected $table = 'invoice_allocations';

    protected $primaryKey = 'ia_id';

    protected $fillable = [
        'tm_id',
        'inv_line_id',
        'return_line_id'
    ];

    public function team(){
        return $this->belongsTo(Team::class,'tm_id','tm_id');
    }

    public function invoiceLine(){
        return $this->belongsTo(InvoiceLine::class,'inv_line_id','inv_line_id');
    }

    public function returnLine(){
        return $this->belongsTo(ReturnLine::class,'return_line_id','return_line_id');
    }
}