<?php
namespace App\CSV;

use App\Models\User;
use App\Models\SubTown;
use App\Models\Town;
use App\Models\Area;
use App\Models\Region;
use App\Models\UserArea as BaseUserArea;

class UserArea extends Allocation {
    protected $title = "User Area Allocations";

    protected $columns = [
        'u_id'=>"Employee code",
        'sub_twn_id'=>"Sub Town Code",
        'twn_id'=>"Town Code",
        'ar_id'=>"Area Code",
        'rg_id'=>"Region Code"
    ];

    protected $tips=[
        "Fill only sub town column if you want to allocate only a sub town. Same as to Areas, Regions,..."
    ];


    protected $allocationsClass=BaseUserArea::class;

    protected $mainClass = User::class;

    protected $childClasses = [
        'sub_twn_id'=>SubTown::class,
        'twn_id'=>Town::class,
        'ar_id'=>Area::class,
        'rg_id'=>Region::class
    ];

    protected function getMainKeyName(){
        return 'u_id';
    }

}