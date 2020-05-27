<?php

namespace App\Ext\Get;

use LaravelTreats\Model\Traits\HasCompositePrimaryKey;
use App\Models\SalesmanValidCustomer;
use App\Models\User;
use App\Models\Chemist;

class SalesmanValidCust extends Get
{
    // protected $connection = 'mysql2';

    use HasCompositePrimaryKey;

    protected $table = 'ifsapp.EXT_SALESMAN_VALID_CUST_UIV';

    public $hasCompositePrimary = true;

    public $primaryKey = ['salesman_code','customer_id'];

    public $hasPrimary = true;

    public function afterCreate($inst, $data)
    {
        $this->createDuplicate($data);
    }

    protected function createDuplicate($data){
        $exist = SalesmanValidCustomer::where('salesman_code','=',$data['salesman_code'])
            ->where('customer_id','=',$data['customer_id'])->latest()->first();

        $user = User::where('u_code','=',$data['salesman_code'])
            ->where('u_tp_id','=',config("shl.sales_rep_type"))
            ->latest()->first();   
        if($user)
            $data['u_id']= $user->getKey();

        $chemist = Chemist::where('chemist_code','=',$data['customer_id'])->latest()->first();
        if($chemist)
            $data['chemist_id']= $chemist->getKey();

        if($exist){
            $exist->update($data);
        } else {
            SalesmanValidCustomer::create($data);
        }
    }

    public function afterUpdate($inst, $data)
    {
        $this->createDuplicate($data);
    }
}
