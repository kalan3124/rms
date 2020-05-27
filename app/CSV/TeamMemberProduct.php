<?php
namespace App\CSV;

use App\Models\TeamUserProduct;
use App\Models\User;
use App\Exceptions\WebAPIException;
use App\Models\TeamUser;
use App\Models\Product;
use App\Models\TeamProduct;

class TeamMemberProduct extends Base{
    protected $title = "Team Member Product Allocations";

    protected $teamId;

    protected $columns = [
        'tmu_id'=>"Employee code",
        'tmp_id'=>"Product Code"
    ];

    protected function beforeInsert(){
        // $mainIds = array_column($this->data,'tmu_id');

        // TeamUserProduct::whereIn('tmu_id',$mainIds)->delete();
    }

    protected function formatValue($columnName, $value)
    {
        if($columnName=='tmu_id'){
            $existsUser = User::where('u_code','like',trim($value))->first();

            if(!$existsUser) throw new WebAPIException("User not found for '$value' code.");

            $teamUser = TeamUser::where('u_id',$existsUser->getKey())->latest()->first();

            if(!$teamUser) throw new WebAPIException($existsUser->getName()."($value) user is not in a team.");

            $this->teamId = $teamUser->tm_id;

            return $teamUser->getKey();
        } else {
            $existsProduct = Product::where('product_code','like',trim($value))->first();

            if(!$existsProduct) throw new WebAPIException("Product not found for '$value' code.");

            $teamProduct = TeamProduct::where('product_id',$existsProduct->getKey())->where('tm_id',$this->teamId)->latest()->first();

            if(!$teamProduct) throw new WebAPIException($existsProduct->product_name."($value) product is not in a team.");

            return $teamProduct->getKey();
        }
    }

    protected function insertRow($row)
    {
        TeamUserProduct::firstOrCreate($row);
    }


}