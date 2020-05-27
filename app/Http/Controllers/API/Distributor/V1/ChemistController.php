<?php 

namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Chemist;
use App\Ext\Customer;
use Validator;
use App\Exceptions\DisAPIException;
use App\Models\Area;
use App\Models\DistributorCustomer;
use App\Models\DistributorSrCustomer;
use App\Models\Route;


use App\Traits\SalesTerritory;
use App\Models\UserAttendance;

class ChemistController extends Controller
{

    use SalesTerritory;

    public function chemists(Request $request){
        $user= Auth::user();

        $attendance = UserAttendance::where('u_id', $user->getKey())
                    ->latest()
                    ->first();

        if(!isset($attendance->checkout_status)){
            $time = strtotime($attendance->check_in_time);
            $getAllocatedTerritories = $this->getRoutesByItinerary($user,$time);
        } else {
            $getAllocatedTerritories = $this->getRoutesByItinerary($user);
        }

        $getAllocatedTerritories = $this->getRoutesByItinerary($user);
        
        $routeChemist =  DistributorCustomer::whereIn('route_id',$getAllocatedTerritories->pluck('route_id')->all())->with('sub_town','sales_price_lists')->get();
       
        $routeChemist->transform(function($rc){
            
                return [
                    'chem_id'=>$rc->dc_id,
                    'chem_code'=>$rc->dc_code,
                    'chem_name'=>$rc->dc_name,
                    'chem_address'=>$rc->dc_address?$rc->dc_address:"",
                    'town_id'=>$rc->sub_town->sub_twn_id??0,
                    'town_name'=>$rc->sub_town?$rc->sub_town->sub_twn_name:"",
                    'chem_class_id'=>"",
                    'chem_class_name'=>"",
                    'chem_type_id'=>"",
                    'chem_type'=>"",
                    'chem_market_description_id'=>"",
                    'chem_market_description'=>"",
                    'chem_price_group'=>isset($rc->sales_price_lists->sales_price_group_id)?$rc->sales_price_lists->sales_price_group_id:"",
                    'chem_price_list'=>isset($rc->sales_price_lists->price_list_no)?$rc->sales_price_lists->price_list_no:"",
                    "lat"=>$rc->dc_lat?$rc->dc_lon:"",
                    "lon"=>$rc->dc_lon?$rc->dc_lat:"",
                    "image_url"=>$rc->dc_image_url?url($rc->dc_image_url):"",
                    "phone_no"=>"",
                    "mobile_number"=>"",
                    "email"=>"",
                    "chemist_owner"=>""
                ];
        });

        return [
            'result'=>true,
            'chemists'=>$routeChemist,
            'count'=>$routeChemist->count()
        ];

    }

    public function unplanned_chemist(Request $request){
        $validator = Validator::make($request->all(),['chemist_name'=>'required']);
        if($validator->fails()){
            // << SE1 >> \\
            throw new DisAPIException("Enter Chemist Name!",4);
        }

        $user= Auth::user();

        $getAllocatedTerritories = $this->getRoutesByItinerary($user);

        $allocatedChemist = DistributorCustomer::with('sub_town','sales_price_lists')->where('route_id','!=',$getAllocatedTerritories->pluck('route_id')->all())->where('dc_name','LIKE','%'.$request->chemist_name.'%')->get();
        
        $allocatedChemist->transform(function($rc){

            return [
                'chem_id'=>$rc->dc_id,
                'chem_code'=>$rc->dc_code,
                'chem_name'=>$rc->dc_name,
                'chem_address'=>$rc->dc_address?$rc->dc_address:"",
                'town_id'=>$rc->sub_town->sub_twn_id??0,
                'town_name'=>$rc->sub_town?$rc->sub_town->sub_twn_name:"",
                'chem_class_id'=>"",
                'chem_class_name'=>"",
                'chem_type_id'=>"",
                'chem_type'=>"",
                'chem_market_description_id'=>"",
                'chem_market_description'=>"",
                'chem_price_group'=>isset($rc->sales_price_lists->sales_price_group_id)?$rc->sales_price_lists->sales_price_group_id:"",
                'chem_price_list'=>isset($rc->sales_price_lists->price_list_no)?$rc->sales_price_lists->price_list_no:"",
                "lat"=>$rc->dc_lat?$rc->dc_lon:"",
                "lon"=>$rc->dc_lon?$rc->dc_lat:"",
                "image_url"=>$rc->dc_image_url?url($rc->dc_image_url):"",
                "phone_no"=>"",
                "mobile_number"=>"",
                "email"=>"",
                "chemist_owner"=>""
            ];
        });

        return [
            'result'=>true,
            'chemists'=>$allocatedChemist,
            'count'=>$allocatedChemist->count()
        ];

    }
}