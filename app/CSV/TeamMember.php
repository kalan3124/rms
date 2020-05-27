<?php
namespace App\CSV;

use App\Models\User;
use App\Models\TeamUser;
use App\Models\Team;

class TeamMember extends Allocation {
    protected $title = "Team Member Allocations";

    protected $columns = [
        'u_id'=>"Employee code",
        'tm_id'=>"Team Code"
    ];


    protected $allocationsClass=TeamUser::class;

    protected $mainClass = Team::class;

    protected $childClasses = [
        'u_id'=>User::class,
    ];

}