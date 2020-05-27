<?php

namespace App\Models;

class SfaExpenses extends Base
{
     protected $table = 'sfa_expenses';

     protected $primaryKey = 'sfa_exp_id';
 
     protected $fillable =[
         'bt_id',
         'stationery',
         'parking',
         'remark',
         'app_version',
         'exp_time',
         'u_id',
         'mileage',
         'aprroved',
         'approved_u_id',
         'def_actual_mileage',
         'actual_mileage',
         'mileage_amount'
     ];

    public function user(){
        return $this->hasOne(User::class,'id','u_id');
    }

    public function bataType(){
        return $this->hasOne(BataType::class,'bt_id','bt_id');
    }
}
?>