<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorPaymentInvoice extends Model
{
    protected $table = 'distributor_payment_invoices';

    protected $primaryKey = 'id';

    protected $fillable = [
        'distributor_payment_id','di_id','amount'
    ];

    public function payment(){
        return $this->belongsTo(DistributorPayment::class,'distributor_payment_id');
    }

    public function invoice(){
        return $this->belongsTo(DistributorInvoice::class,'di_id');
    }
}
