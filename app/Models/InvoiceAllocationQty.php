<?php
namespace App\Models;

class InvoiceAllocationQty extends Base {
    protected $table = 'invoice_allocation_qty';

    protected $primaryKey = 'iaq_id';

    protected $fillable = [
        'ia_id',
        'tm_id',
        'u_id',
        'iaq_qty',
    ];

    public function invoiceAllocation(){
        return $this->belongsTo(InvoiceAllocation::class,'ia_id','ia_id');
    }

    public function team(){
        return $this->belongsTo(Team::class,'tm_id','tm_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}