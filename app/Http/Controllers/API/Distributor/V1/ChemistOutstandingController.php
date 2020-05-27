<?php
namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Traits\SalesTerritory;
use App\Models\Chemist;
use App\Ext\Customer;
use App\Models\SfaSalesOrder;

class ChemistOutstandingController extends Controller{
     use SalesTerritory;

     public function getChemistOutstand(Request $request){

          $user= Auth::user();

          $getAllocatedTerritories = $this->getRoutesByItinerary($user);
          $routeChemist = Chemist::whereIn('route_id',$getAllocatedTerritories->pluck('route_id')->all())->get();
        
          $routeChemist->transform(function($val) use($user){

               $limit = Customer::where('customer_id',$val->chemist_code)->first();
            
               $last_visit = SfaSalesOrder::where('chemist_id',$val->chemsit_id)->where('u_id',$user->getKey())->latest()->first();
               
               return[
                    "outstanding"=>0.00,
                    "credit_limit"=>isset($limit->credit_limit)?$limit->credit_limit:0.00,
                    "chem_id"=> $val->chemist_id,
                    "last_visit" => isset($last_visit->order_date)?$last_visit->order_date:"-"
               ];
          });

          return[
               'result' => true,
               'data'=> $routeChemist
          ];
     }
}
?>