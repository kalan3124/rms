<?php
namespace App\CSV;

use App\Models\User;
use App\Exceptions\WebAPIException;
use App\Models\Limit;

class ParkingLimit extends Base {
    protected $title = "Parking Limits";

    protected $columns = [
        'u_id'=>"User Code",
        "limit"=>"Limit"
    ];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'u_id':
                if(!$value)
                    throw new WebAPIException("Please provide a user code!");
                $user = User::where((new User)->getCodeName(),"LIKE",$value)->first();
                if(!$user)
                    throw new WebAPIException("User not found! Given user code is '$value'");
                return $user->getKey();
            default:
                return (float) ($value<=0||!$value)?null:$value;
        }
    }

    protected function insertRow($row)
    {
        $user = User::find($row['u_id']);

        $user->u_prking_limit = $row['limit'];

        $user->save();

        Limit::create([
            'lmt_ref_id'=>$user->getKey(),
            'lmt_main_type'=>1,
            'lmt_sub_type'=>2,
            'lmt_min_amnt'=>$row['limit'],
            'lmt_frequency'=>1,
            'lmt_start_at'=>date('Y-m-d')
        ]);
    }

}