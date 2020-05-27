<?php
namespace App\CSV;

use App\Models\User;
use App\Models\Area;
use App\Exceptions\WebAPIException;
use App\Models\DistributorCustomer;
use App\Models\Route;

class RouteCustomer extends Allocation {
    protected $title = "Route Customer Allocations";

    protected $columns = [
        'route_id'=>"Route Code",
        'dc_id'=>"Customer Code",
    ];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'route_id':
                if(!$value)
                    throw new WebAPIException("Please provide a Route code!");
                $route = Route::where('route_code',"LIKE",$value)->first();
                if(!$route)
                    throw new WebAPIException("Route not found! Given route code is '$value'");
                return $route->getKey();
            case 'dc_id': 
                if(!$value)
                    throw new WebAPIException("Please provide a Customer code!");
                $customer = DistributorCustomer::where('dc_code',"LIKE",$value)->first();
                if(!$customer)
                    throw new WebAPIException("Customer not found! Given customer code is '$value'");
                return $customer->getKey();
            default:
                return ($value<=0||!$value)?null:$value;
        }
    }

    protected function insertRow($row)
    {
         if(isset($row['dc_id']) && isset($row['route_id'])){
               $customer = DistributorCustomer::where('dc_id',$row['dc_id'])->first();
               $customer->route_id = $row['route_id'];
               $customer->save();
         }
    }

}