<?php 
namespace App\Models;

class SalesmanValidCustomer extends Base{

    protected $table = 'salesman_valid_customer';

    protected $primaryKey = 'smv_cust_id';

    protected $fillable = [
        'smv_cust_id',
        'salesman_code',
        'customer_id',
        'from_date',
        'to_date',
        'last_updated_on',
        'u_id',
        'chemist_id'
    ];

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }

    public function customer(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }
}