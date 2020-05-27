<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserCustomer extends Base
{
    protected $table = 'user_customer';

    protected $primaryKey = 'uc_id';

    protected $fillable = [
        'doc_id','chemist_id','u_id','hos_stf_id'
    ];

    public function doctor(){
        return $this->belongsTo(Doctor::class,'doc_id','doc_id');
    }

    public function chemist(){
        return $this->belongsTo(Chemist::class,'chemist_id','chemist_id');
    }

    public function otherHospitalStaff(){
        return $this->belongsTo(OtherHospitalStaff::class,'hos_stf_id','hos_stf_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'u_id','id');
    }
    /**
     * Returning customers by user
     *
     * @param User $user
     * @param int $type 0=all 1=chemists 2=doctors 3=other hospital staffs
     * @return Collection
     */
    public static function getByUser($user,$type=0,$with=[]){

        $users = User::getByUser($user);

        $userCustomersQuery = UserCustomer::whereIn('u_id',$users->pluck('id')->all())->with(['chemist','doctor','otherHospitalStaff'])->with($with);

        switch ($type) {
            case 1:
                $userCustomersQuery->whereNotNull('chemist_id');
                break;
            case 2:
                $userCustomersQuery->whereNotNull('doc_id');
                break;
            case 3:
                $userCustomersQuery->whereNotNull('hos_stf_id');
        }

        $userCustomers = $userCustomersQuery->get();

        $userCustomers = $userCustomers->filter(function($userCustomer)use($type){
            if($type==1&&!isset($userCustomer->chemist))
                return false;
            if($type==2&&!isset($userCustomer->doctor))
                return false;
            if($type==3&&!isset($userCustomer->otherHospitalStaff))
                return false;
                
            if(!isset($userCustomer->chemist)&&!isset($userCustomer->doctor)&&!isset($userCustomer->otherHospitalStaff))
                return false;

            return true;
        });

        return $userCustomers;

    }
}
