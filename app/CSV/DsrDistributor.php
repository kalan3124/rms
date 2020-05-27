<?php
namespace App\CSV;

use App\Models\User;
use App\Models\DistributorSalesRep;

class DsrDistributor extends Allocation {
    protected $title = "Dsr Distributor Allocations";

    protected $columns = [
        'sr_id'=>"DSR Code",
        'dis_id'=>"Distributor Code",
    ];


    protected $allocationsClass=DistributorSalesRep::class;

    protected $mainClass = User::class;

    protected $childClasses = [
        'dis_id'=>User::class
    ];

    protected function getMainKeyName(){
        return 'sr_id';
    }

}