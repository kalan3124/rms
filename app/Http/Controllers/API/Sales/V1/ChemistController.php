<?php 

namespace App\Http\Controllers\API\Sales\V1;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Chemist;
use App\Ext\Customer;
use Validator;
use App\Exceptions\SalesAPIException;
use App\Models\Area;
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

        if(!$attendance->checkout_status){
            $time = strtotime($attendance->check_in_time);
            $getAllocatedTerritories = $this->getRoutesByItinerary($user,$time);
        } else {
            $getAllocatedTerritories = $this->getRoutesByItinerary($user);
        }

        $routeChemist = Chemist::whereIn('route_id',$getAllocatedTerritories->pluck('route_id')->all())->get();
        
        $routeChemist->transform(function($rc){

            $Chemist = Chemist::with('sub_town','chemist_class','chemist_types')->where('chemist_id',$rc->chemist_id)->first();
            
            $cusPriceGroup = Customer::where('customer_id',$Chemist->chemist_code)->latest()->first();
                return [
                    'chem_id'=>$Chemist->chemist_id,
                    'chem_code'=>$Chemist->chemist_code,
                    'chem_name'=>$Chemist->chemist_name,
                    'chem_address'=>$Chemist->chemist_address?$Chemist->chemist_address:"",
                    'town_id'=>$Chemist->sub_twn_id??0,
                    'town_name'=>$Chemist->sub_town?$Chemist->sub_town->sub_twn_name:"",
                    'chem_class_id'=>$Chemist->chemist->chemist_class_id??0,
                    'chem_class_name'=>$Chemist->chemist_class?$Chemist->chemist_class->chemist_class_name:"",
                    'chem_type_id'=>$Chemist->chemist_type_id??0,
                    'chem_type'=>$Chemist->chemist_types?$Chemist->chemist_types->chemist_type_name:"",
                    'chem_market_description_id'=>$Chemist->chemist_mkd_id??0,
                    'chem_market_description'=>$Chemist->chemist_market_description?$Chemist->chemist_market_description->chemist_mkd_name:"",
                    'chem_price_group'=>$cusPriceGroup?$cusPriceGroup->cust_price_grp:"",
                    'chem_price_list'=>$cusPriceGroup?$cusPriceGroup->sfa_price_list:"",
                    "lat"=>$Chemist->lat?$Chemist->lat:"",
                    "lon"=>$Chemist->lon?$Chemist->lon:"",
                    // "image_url"=>$Chemist->image_url?"http://shl.salespad.lk/healthcare".$Chemist->image_url:"",
                    "image_url"=>$Chemist->image_url?url($Chemist->image_url):"",
                    "phone_no"=>$Chemist->phone_no?$Chemist->phone_no:"",
                    "mobile_number"=>$Chemist->mobile_number?$Chemist->mobile_number:"",
                    "email"=>$Chemist->email?$Chemist->email:"",
                    "chemist_owner"=>$Chemist->chemist_owner?$Chemist->chemist_owner:""
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
            throw new SalesAPIException("Enter Chemist Name!",4);
        }

        $user= Auth::user();

        try {
            $getAllocatedTerritories = $this->getRoutesByItinerary($user);
        } catch (\Throwable $th) {
            $getAllocatedTerritories = [];
        }

        if(!$getAllocatedTerritories){
            
            $allocatedChemist = DB::table('salesman_valid_customer AS svc')
                ->join('chemist AS c', 'c.chemist_id','svc.chemist_id')
                ->where('u_id', $user->getKey())
                ->where('c.chemist_name','like', '%'.$request->chemist_name.'%')
                ->whereDate('svc.from_date','<=',date('Y-m-d'))
                ->whereDate('svc.to_date','>=',date('Y-m-d'))
                ->whereNull('c.deleted_at')
                ->whereNull('svc.deleted_at')
                ->get();

        } else {

            $userAreaCode = substr($user->u_code, 0, 4);
            $userArea = Area::where('ar_code','=',$userAreaCode)->first();

            //Get Routes without today route
            $routes = Route::where('ar_id',$userArea->ar_id)
                ->whereNotIn('route_id',$getAllocatedTerritories->pluck('route_id')->all())
                ->get();

            $allocatedChemist = DB::table('salesman_valid_customer AS svc')
                ->join('chemist AS c', 'c.chemist_id','svc.chemist_id')
                ->where('u_id', $user->getKey())
                ->where('c.chemist_name','like', '%'.$request->chemist_name.'%')
                ->whereDate('svc.from_date','<=',date('Y-m-d'))
                ->whereDate('svc.to_date','>=',date('Y-m-d'))
                ->whereIn('route_id',$routes->pluck('route_id')->all())
                ->groupBy('c.chemist_id')
                ->whereNull('c.deleted_at')
                ->whereNull('svc.deleted_at')
                ->get();  

        }

        $allocatedChemist->transform(function($rc){

            $Chemist = Chemist::with('sub_town','chemist_class','chemist_types')->where('chemist_id',$rc->chemist_id)->first();

            $cusPriceGroup = Customer::where('customer_id',$Chemist->chemist_code)->latest()->first();
                return [
                    'chem_id'=>$Chemist->chemist_id,
                    'chem_code'=>$Chemist->chemist_code,
                    'chem_name'=>$Chemist->chemist_name,
                    'chem_address'=>$Chemist->chemist_address?$Chemist->chemist_address:"",
                    'town_id'=>$Chemist->sub_twn_id??0,
                    'town_name'=>$Chemist->sub_town?$Chemist->sub_town->sub_twn_name:"",
                    'chem_class_id'=>$Chemist->chemist->chemist_class_id??0,
                    'chem_class_name'=>$Chemist->chemist_class?$Chemist->chemist_class->chemist_class_name:"",
                    'chem_type_id'=>$Chemist->chemist_type_id??0,
                    'chem_type'=>$Chemist->chemist_types?$Chemist->chemist_types->chemist_type_name:"",
                    'chem_market_description_id'=>$Chemist->chemist_mkd_id??0,
                    'chem_market_description'=>$Chemist->chemist_market_description?$Chemist->chemist_market_description->chemist_mkd_name:"",
                    'chem_price_group'=>$cusPriceGroup?$cusPriceGroup->cust_price_grp:"",
                    'chem_price_list'=>$cusPriceGroup?$cusPriceGroup->sfa_price_list:"",
                    "lat"=>$Chemist->lat?$Chemist->lat:"",
                    "lon"=>$Chemist->lon?$Chemist->lon:"",
                    "image_url"=>$Chemist->image_url?url($Chemist->image_url):"",
                    "phone_no"=>$Chemist->phone_no?$Chemist->phone_no:"",
                    "mobile_number"=>$Chemist->mobile_number?$Chemist->mobile_number:"",
                    "email"=>$Chemist->email?$Chemist->email:"",
                    "chemist_owner"=>$Chemist->chemist_owner?$Chemist->chemist_owner:""
                ];
        });

        return [
            'result'=>true,
            'chemists'=>$allocatedChemist,
            'count'=>$allocatedChemist->count()
        ];

    }
}