<?php
namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use Validator;
use App\Exceptions\WebAPIException;
use App\Models\TeamProduct;
use App\Models\TeamUserProduct;
use App\Http\Controllers\Controller;
use App\Models\Principal;
use App\Models\Product;

class TeamProductController extends Controller{
    public function save(Request $request){
        $validation = Validator::make($request->all(),[
            'teams'=>'required|array',
            'products'=>'required|array',
            'teams.*.value'=>'required|numeric|exists:teams,tm_id',
            'products.*.value'=>'required|numeric|exists:product,product_id',
        ]);

        if($validation->fails()){
            throw new WebAPIException("We can not validate your request.");
        }

        $teams = $request->input('teams');
        $products = $request->input('products');

        foreach($teams as $team){
            $teamProducts =  TeamProduct::where('tm_id',$team['value'])->get();

            $teamProductIds = $teamProducts->pluck('tmp_id')->all();

            TeamUserProduct::whereIn('tmp_id',$teamProductIds)->delete();
            TeamProduct::whereIn('tmp_id',$teamProductIds)->delete();

            foreach($products as $product){
                TeamProduct::create([
                    'tm_id'=>$team['value'],
                    'product_id'=>$product['value']
                ]);
            }
        }

        return response()->json([
            'success'=>true,
            'message'=>"You have successfully allocated the given products to given teams."
        ]);
    }

    public function load(Request $request){
        $validation = Validator::make($request->all(),[
            'team'=>'required|array',
            'team.value'=>'required|numeric'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request");
        }

        $teamId = $request->input('team.value');

        $products = TeamProduct::where('tm_id',$teamId)->with('product')->get();

        $products->transform(function(TeamProduct $teamProduct){
            if(!$teamProduct->product)
                return null;

            return [
                'value'=>$teamProduct->product->getKey(),
                'label'=>$teamProduct->product->product_name
            ];
        });

        $products = $products->filter(function($product){
            return !!$product;
        })->values();

        return response()->json([
            'success'=>true,
            'products'=>$products
        ]);
    }

    public function loadProductByPrincipal(Request $request){
        $keyword = $request->input('keyword',"");
    
        $products = Product::where('principal_id',$request->input('principal.value'))
        ->where(function($query) use($keyword){
            $query->orWhere("product_code","LIKE","%$keyword%");
            $query->orWhere("product_name","LIKE","%$keyword%");

        })
        ->get();

        $products->transform(function($product){
            return [
                'value'=>$product->product_id,
                'label'=>$product->product_name
            ];
        });
        return $products;
    }
}