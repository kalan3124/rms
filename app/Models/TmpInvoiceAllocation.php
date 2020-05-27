<?php

namespace App\Models;

class TmpInvoiceAllocation extends Base {
    protected $table = 'tmp_invoice_allocation';

    protected $primaryKey = 'tia_id';

    protected $fillable = [
        'ia_id',
        'inv_line_id',
        'return_line_id',
        'u_id',
        'tia_qty',
    ];

    public function invoiceAllocation(){
        return $this->belongsTo(InvoiceAllocation::class,'ia_id','ia_id');
    }

    public function invoiceLine(){
        return $this->belongsTo(InvoiceLine::class,'inv_line_id','inv_line_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
}