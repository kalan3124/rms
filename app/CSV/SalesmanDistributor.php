<?php
namespace App\CSV;

use App\Models\User;
use App\Models\SubTown;
use App\Models\Town;
use App\Models\Area;
use App\Models\DistributorCustomer;
use App\Models\DistributorSalesMan;
use App\Models\DistributorSalesRep;
use App\Models\DistributorSrCustomer;
use App\Models\Region;
use App\Models\UserArea as BaseUserArea;

class SalesmanDistributor extends Allocation {
    protected $title = "Salesman Distributor Allocation";

    protected $columns = [
        'sr_id'=>"Sales Man Code",
        'dis_id'=>"Distributor Code",
    ];


    protected $allocationsClass=DistributorSalesMan::class;

    protected $mainClass = User::class;

    protected $childClasses = [
        'dis_id'=>User::class
    ];

    protected function getMainKeyName(){
        return 'sr_id';
    }

}