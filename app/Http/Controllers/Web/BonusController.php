<?php
namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\BonusDistributor;
use App\Models\BonusProduct;
use App\Models\BonusRatio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonusController extends Controller {
    public function save(Request $request){
        $validations = Validator::make($request->all(),[
            'description'=>'required',
            'code'=>'required',
            'ratios'=>'required|array',
            'ratios.*.minQty'=>'required|numeric',
            'ratios.*.maxQty'=>'required|numeric',
            'ratios.*.purchaseQty'=>'required|numeric',
            'ratios.*.freeQty'=>'required|numeric',
            'products'=>'required|array',
            'products.*.value'=>'required|numeric|exists:product,product_id',
            'freeProducts'=>'required|array',
            'freeProducts.*.value'=>'required|numeric|exists:product,product_id',
            'fromDate'=>'required',
            'toDate'=>'required',
            'distributors'=>'required|array',
            'distributors.*.value'=>'required|numeric|exists:users,id'
        ]);

        if($validations->fails()){
            throw new WebAPIException("Invalid request. Please check again!");
        }

        $code = $request->input('code');
        $distributors = $request->input('distributors');
        $ratios = $request->input('ratios');
        $products = $request->input('products');
        $freeProducts = $request->input('freeProducts');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        try {

            DB::beginTransaction();

            $bonus = Bonus::create([
                'bns_code'=>$code,
                'bns_start_date'=>$fromDate,
                'bns_end_date'=>$toDate,
                'bns_all'=>empty($distributors)?1:0
            ]);

            if(!empty($distributors)){
                foreach ($distributors as $key => $distributor) {
                    BonusDistributor::create([
                        'bns_id'=>$bonus->getKey(),
                        'dis_id'=>$distributor['value']
                    ]);
                }
            }

            foreach ($ratios as $key => $ratio) {
                BonusRatio::create([
                    'bnsr_min'=>$ratio['minQty'],
                    'bnsr_max'=>$ratio['maxQty'],
                    'bnsr_purchase'=>$ratio['purchaseQty'],
                    'bnsr_free'=>$ratio['freeQty']
                ]);
            }

            foreach ($products as $key => $product) {
                BonusProduct::create([
                    'bns_id'=>$bonus->getKey(),
                    'product_id'=>$product['value']
                ]);
            }

            foreach ($freeProducts as $key => $product) {
                BonusProduct::create([
                    'bns_id'=>$bonus->getKey(),
                    'product_id'=>$product['value']
                ]);
            }

            DB::commit();

        } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>"Can not save the bonus scheme. Server error occured."
            ]);
        }

        return response()->json([
            'success'=>true,
            'message'=>"Successfully saved your bonus scheme"
        ]);

    }
}