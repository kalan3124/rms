<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Http\Controllers\Controller;
use App\Models\DsrProduct;
use Illuminate\Http\Request;

class UserProductController extends Controller {
    public function loadProducts(Request $request){
        $sr = $request->input('sr');

        $products = DsrProduct::with('product')->where('dsr_id',$sr)->get();

        $products->transform(function($dsrProduct){
            if(!$dsrProduct->product)
                return null;

            return [
                'value'=>$dsrProduct->product->getKey(),
                'label'=>$dsrProduct->product->product_name,
            ];
        });

        $products = $products->filter(function($product){
            return !!$product;
        });

        return $products;
    }

    public function save(Request $request){
        $products = $request->input('products');
        $srs = $request->input('srs');


        foreach ($srs as $key => $sr) {
            DsrProduct::where('dsr_id',$sr['value'])->delete();
            foreach ($products as $key => $product) {


                DsrProduct::create([
                    'dsr_id'=>$sr['value'],
                    'product_id'=>$product['value']
                ]);
            }
        }

        return [
            'success'=>true,
            'message'=>"You have successfully allocated products."
        ];
    }
}