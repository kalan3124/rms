<?php
namespace App\Models;

class Invoice extends Base{
    protected $table='invoice';

    protected $primaryKey='inv_head_id';
   
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
        'last_updated_on',
        'chemist_id'
    ];

    public function invoice_line(){
        return $this->hasMany(InvoiceLine::class,'inv_head_id','inv_head_id');
    }
    
    public function customer(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }

    public function return_line(){
        return $this->hasmany(ReturnLine::class,'inv_head_id','inv_head_id');
    }
}