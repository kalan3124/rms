<?php
namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\WebAPIException;
use App\Models\Chemist;
use App\Models\Route;
use App\Models\RouteChemist;
use Illuminate\Support\Facades\DB;

class RouteChemistController extends Controller {
    public function load (Request $request){
        $validation = Validator::make($request->all(),[
            'route'=>'required|numeric|exists:sfa_route,route_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please select a route to load customers.");
        }

        $chemists = Chemist::where('route_id',$request->input('route'))->get();


        $chemists = $chemists->map(function(Chemist $chemist){
            return [
                'value'=>$chemist->getKey(),
                'label'=>'[ '.$chemist->chemist_code.' ] '.$chemist->chemist_name,
            ];
        });

        return $chemists->values();
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

        $chemists = $request->input('chemists');
        $routes = $request->input('routes');

        $route = $routes[0];

        Chemist::where('route_id',$route['value'])->update([
            'route_id'=> NULL
        ]);

        foreach ($chemists as  $chemist) {

            $chemist = Chemist::where('chemist_id',$chemist['value'])->first();
            $chemist->route_id = $route['value'];
            $chemist->save();
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
        ->where('route_type',0)->get();
        
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

        if($request->input('area.value') == '14') {

            $chemist = DB::table('chemist AS c')
            ->join('ext_customer_uiv AS ec','ec.customer_id','c.chemist_code')
            ->join('area AS a','a.ar_code','ec.region')
            ->where('a.ar_id',$request->input('area.value'))
            ->where(function($query) use($keyword){
                $query->orWhere("c.chemist_code","LIKE","%$keyword%");
                $query->orWhere("c.chemist_name","LIKE","%$keyword%");

            })
            ->whereNull('c.deleted_at')
            ->whereNull('ec.deleted_at')
            ->whereNull('a.deleted_at')
            ->limit(30)
            ->get();
        } else {

            $chemist = DB::table('town AS t')
            ->join('sub_town AS st','t.twn_id','st.twn_id')
            ->join('chemist AS c','c.sub_twn_id','st.sub_twn_id')
            ->where('t.ar_id',$request->input('area.value'))
            ->where(function($query) use($keyword){
                $query->orWhere("chemist_code","LIKE","%$keyword%");
                $query->orWhere("chemist_name","LIKE","%$keyword%");

            })->whereNull('t.deleted_at')
            ->whereNull('st.deleted_at')
            ->whereNull('c.deleted_at')
            ->limit(30)
            ->get();

        }

        $chemist->transform(function($chem){
            return [
                'value'=>$chem->chemist_id,
                'label'=>'[ '.$chem->chemist_code.' ] '.$chem->chemist_name
            ];
        });
        
        return $chemist;
    }
}