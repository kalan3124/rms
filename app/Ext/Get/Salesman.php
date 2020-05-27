<?php

namespace App\Ext\Get;

use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\UserType;

class Salesman extends Get
{
    // protected $connection = 'mysql2';

    protected $table = 'ifsapp.EXT_SALESMAN_UIV'; 

    protected $primaryKey ='salesman_code';

    public $originalModelName = User::class;

    public $codeName = 'salesman_code';

    public function afterCreate($inst, $data)
    {
        $this->createDuplicate($data);
    }

    protected function createDuplicate($data){

        $exist = User::where('u_code','=',$data['salesman_code'])->latest()->first();

        $user_type = UserType::where('u_tp_id','=',config("shl.sales_rep_type"))->first();
        if($user_type)
            $data['u_tp_id'] = $user_type->getKey();

            $data['user_name'] = $data['salesman_code'];
            $data['u_code'] = $data['salesman_code'];
            $data['password'] = Hash::make($data['salesman_code']);

        if($exist){
            $exist->update($data);
        } else {
            User::create($data);
        }
    }

    public function afterUpdate($inst, $data)
    {
        $this->createDuplicate($data);
    }

}
