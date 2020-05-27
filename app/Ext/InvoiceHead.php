<?php
namespace App\Ext;

use App\Models\Base;

class InvoiceHead extends Base
{
    protected $revisionEnabled = false;
    
    protected $table = 'ext_invoice_head_uiv';

    protected $primarykey = 'inv_head_id';

    protected $fillable = [
        'company',
        'customer_no',
        'customer_name',
        'invoice_series',
        'invoice_no',
        'site',
        'currency',
        'order_no',
        'created_date',
        'gross_amount',
        'customer_po_no',
        'last_updated_on'
    ];

    public function invoice_line(){
        return $this->hasMany(InvoiceLines::class,'invoice_no','invoice_no');
    }
    public function customer(){
        return $this->belongsTo(Chemist::class,'customer_no','chemist_code');
    }
    
}
