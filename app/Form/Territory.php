<?php 
namespace App\Form;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\Territory as TerritoryTrait;

class Territory Extends Form {
    use TerritoryTrait;
    /**
     * Returning the allocated areas for a user
     *
     * @param int $userId
     * @return Illuminate\Support\Collection
     */
    protected function getUserAreas($userId){
        $user = User::find($userId);

        return $this->getAllocatedTerritories($user);
    }

}