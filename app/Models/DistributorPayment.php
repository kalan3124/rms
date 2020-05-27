<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorPayment extends Model
{
    protected $table = 'distributor_payments';

    protected $primaryKey = 'id';

    protected $codeName = 'p_code';

    protected $fillable = [
        'dc_id',
        'amount',
        'balance',
        'date',
        'u_id',
        'payment_type_id',
        'p_code',
        'c_no',
        'c_bank',
        'c_branch',
        'c_date',
    ];

    public function lines(){
        return $this->hasMany(DistributorPaymentInvoice::class,'distributor_payment_id','id');
    }

    public function customer(){
        return $this->belongsTo(DistributorCustomer::class, 'dc_id');
    }

    public function type(){
        return $this->belongsTo(PaymentType::class, 'payment_type_id');
    }

    public static function generateNumber(){
        $paymentCount = self::count();

        return 'PYM/'.($paymentCount+1);
    }
}
