<?php
namespace App\CSV;

use App\Models\UserCustomer as AppUserCustomer;
use App\Models\Chemist;
use App\Models\Doctor;
use App\Models\OtherHospitalStaff;
use App\Models\User;

class UserCustomer extends Allocation{
    protected $title = "User Customer Allocations";

    protected $columns = [
        'u_id'=>"Employee code",
        'doc_id'=>"Doctor Code",
        'chemist_id'=>"Chemist Code",
        'hos_stf_id'=>"Hospital Staff Code"
    ];

    protected $tips = [
        "Fill only one customer for each row."
    ];

    protected $allocationsClass=AppUserCustomer::class;

    protected $mainClass = User::class;

    protected $childClasses = [
        'doc_id'=>Doctor::class,
        'chemist_id'=>Chemist::class,
        'hos_stf_id'=>OtherHospitalStaff::class
    ];

    protected function getMainKeyName(){
        return 'u_id';
    }
}