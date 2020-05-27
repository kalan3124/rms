<?php 
namespace App\Http\Controllers\API\Medical\V1;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use App\Traits\Team;
use Illuminate\Http\Request;

class SampleProductController extends Controller{
 
    use Team;

    public function index( Request $request){

        $timestamp = $request->input('timestamp');
        if($timestamp){
            $timestamp = $timestamp/1000;
        }

        $user = Auth::user();
       
        $products = $this->getProductsByUser($user,['brand']);


        $products->transform(function($product)use($timestamp){
            if($timestamp){
                if($product->created_at->timestamp<=$timestamp){
                    return  null;
                }
            }
            return [
                'product_id'=>$product->getKey(),
                'product_name'=>$product->product_name,
                'product_gn'=>$product->brand?$product->brand->brand_name:"",
                'sample_qty'=>9999999
            ];
        });

        $products =$products->filter(function($product){return !!$product;})->values() ;

        return response()->json([
            'result'=>true,
            'products'=>$products,
            'count'=>$products->count()
        ]);
    }
}