<?php
namespace App\CSV;

use App\Models\Team;
use App\Models\Product;
use App\Models\TeamProduct as AppTeamProduct;

class TeamProduct extends Allocation{
    protected $title = "Team Product Allocations";

    protected $columns = [
        'tm_id'=>"Team code",
        'product_id'=>"Product Code"
    ];

    protected $allocationsClass=AppTeamProduct::class;

    protected $mainClass = Team::class;

    protected $childClasses = [
        'product_id'=>Product::class
    ];
}