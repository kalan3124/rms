<?php
namespace App\CSV;

use App\Models\User;
use App\Models\SubTown;
use App\Models\Town;
use App\Models\Area;
use App\Models\DistributorCustomer;
use App\Models\DistributorSrCustomer;
use App\Models\Region;
use App\Models\UserArea as BaseUserArea;

class DsrCustomer extends Allocation {
    protected $title = "Dsr Customer Allocations";

    protected $columns = [
        'u_id'=>"DSR Code",
        'dc_id'=>"Customer Code",
    ];


    protected $allocationsClass=DistributorSrCustomer::class;

    protected $mainClass = User::class;

    protected $childClasses = [
        'dc_id'=>DistributorCustomer::class
    ];

    protected function getMainKeyName(){
        return 'u_id';
    }

}