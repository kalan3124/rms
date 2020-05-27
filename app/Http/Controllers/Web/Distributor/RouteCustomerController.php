<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\WebAPIException;
use App\Models\DistributorCustomer;
use App\Models\Route;
use Illuminate\Support\Facades\DB;

class RouteCustomerController extends Controller {
    public function load (Request $request){
        $validation = Validator::make($request->all(),[
            'route'=>'required|numeric|exists:sfa_route,route_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please select a route to load customers.");
        }

        $customers = DistributorCustomer::where('route_id',$request->input('route'))->get();


        $customers = $customers->map(function(DistributorCustomer $customer){
            return [
                'value'=>$customer->getKey(),
                'label'=>'[ '.$customer->dc_code.' ] '.$customer->dc_name,
            ];
        });

        return $customers->values();
    }

    public function save(Request $request){

        $validation = Validator::make($request->all(),[
            'routes'=>'required|array',
            'routes.*.value'=>'required|numeric|exists:sfa_route,route_id',
            'chemists'=>'required|array',
            'chemists.*.value'=>'required|numeric|exists:chemist,chemist_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $customers = $request->input('chemists');
        $routes = $request->input('routes');

        $route = $routes[0];

        foreach ($customers as  $customer) {
            DistributorCustomer::where('dc_id',$customer['value'])->update([
                'route_id'=>$route['value']
            ]);
        }

        return response()->json([
            'success'=>true,
            'message'=>'Successfully allocated all customers to the route.'
        ]);
    }

    public function loadRoutesByArea(Request $request){

        $validation = Validator::make($request->all(),[
            'area'=>'required'
        ]);
        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please select a area to load routes.");
        }

        $keyword = $request->input('keyword',"");

        $route = Route::where(function($query) use($keyword){
            $query->orWhere("route_code","LIKE","%$keyword%");
            $query->orWhere("route_name","LIKE","%$keyword%");

        })
        ->where('ar_id',$request->input('area.value'))
        ->where('route_type',1)->get();
        
        $route->transform(function($route){
            return [
                'value'=>$route->getKey(),
                'label'=>'[ '.$route->route_code.' ] '.$route->route_name,
            ];
        });

        return $route;
    }

    public function loadChemitsByArea(Request $request){
        $validation = Validator::make($request->all(),[
            'area'=>'required'
        ]);
        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please select a area to load chemists.");
        }

        $keyword = $request->input('keyword',"");

        $customers = DB::table('town AS t')
        ->join('sub_town AS st','t.twn_id','st.twn_id')
        ->join('distributor_customer AS c','c.sub_twn_id','st.sub_twn_id')
        ->where('t.ar_id',$request->input('area.value'))
        ->where(function($query) use($keyword){
            $query->orWhere("dc_code","LIKE","%$keyword%");
            $query->orWhere("dc_name","LIKE","%$keyword%");

        })->whereNull('t.deleted_at')
        ->whereNull('st.deleted_at')
        ->whereNull('c.deleted_at')
        ->limit(30)
        ->get();

        $customers->transform(function($customer){
            return [
                'value'=>$customer->dc_id,
                'label'=>'[ '.$customer->dc_code.' ] '.$customer->dc_name
            ];
        });
        
        return $customers;
    }
}