<?php
namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Models\TeamUser;
use App\Models\UserProductTarget;
use App\Models\UserTarget;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\Team;
use App\Models\User;

class UserTargetController extends Controller
{
    use Team;

    public function load(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'rep' => 'required|array',
            'rep.value' => 'required|numeric|exists:users,id',
            'month'=>'required|date'
        ]);

        if ($validation->fails()) {
            throw new WebAPIException("Can not validate your request. Please provide a medical represenative");
        }

        $rep = $request->input('rep');

        $mainTarget = UserTarget::where('u_id', $rep['value'])
            ->where('ut_month',date('m',strtotime($request->input('month'))))
            ->where('ut_year',date('Y',strtotime($request->input('month'))))
            ->latest()
            ->first();

        $productTargets = collect([]);

        if ($mainTarget) {
            $productTargets = UserProductTarget::where('ut_id', $mainTarget->getKey())->get();
        }

        $teamUser = TeamUser::with([
            'teamUserProducts',
            'teamUserProducts.teamProduct',
            'teamUserProducts.teamProduct.product',
            'teamUserProducts.teamProduct.product.principal',
        ])->where('u_id', $rep['value'])->latest()->first();

        if (!$teamUser) {
            throw new WebAPIException("You haven't assigned any team!");
        }

        $user = User::find($rep['value']);

        $userProducts = $this->getProductsByUser($user,['brand','principal','latestPriceInfo']);

        $products = [];

        $brands = [];

        $principals = [];

        foreach ($userProducts as $product) {
            if (!isset($products[$product->getKey()])) {

                $productTarget = $productTargets->firstWhere('product_id', $product->getKey());

                $price = 0;

                if(isset($product->latestPriceInfo)){                    
                    if(isset($product->latestPriceInfo->lpi_bdgt_sales))
                        $price = $product->latestPriceInfo->lpi_bdgt_sales;
                    else
                        $price = $product->latestPriceInfo->lpi_pg01_sales;

                }

                $products[$product->getKey()] = [
                    "label" => '['.$product->product_code.'] '.shortString($product->product_name,30),
                    "value" => $product->getKey(),
                    'price'=>$price??0.00,
                    "valueTarget" => $productTarget ? $productTarget->upt_value : "0.00",
                    'qtyTarget' => $productTarget ? $productTarget->upt_qty : 0,
                ];
            }

            if (!isset($brands[$product->brand_id]) && $product->brand) {
                $productTarget = $productTargets->firstWhere('brand_id', $product->brand_id);

                $brands[$product->brand_id] = [
                    "label" => shortString($product->brand->brand_name,30),
                    "value" => $product->brand_id,
                    "valueTarget" => $productTarget ? $productTarget->upt_value : "0.00",
                    'qtyTarget' => $productTarget ? $productTarget->upt_qty : 0,
                ];
            }

            if (!isset($principals[$product->principal_id]) && $product->principal) {
                $productTarget = $productTargets->firstWhere('principal_id', $product->principal_id);

                $principals[$product->principal_id] = [
                    "label" => '['.$product->principal->principal_code.'] '.shortString($product->principal->principal_name,30),
                    "value" => $product->principal_id,
                    "valueTarget" => $productTarget ? $productTarget->upt_value : "0.00",
                    'qtyTarget' => $productTarget ? $productTarget->upt_qty : 0,
                ];
            }
        }

        return [
            "products" => $products,
            "brands" => $brands,
            'principals'=>$principals,
            "valueTarget" => $mainTarget ? $mainTarget->ut_value : "0.00",
            "qtyTarget" => $mainTarget ? $mainTarget->ut_qty : 0,
        ];
    }

    public function save(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'rep' => 'required|array',
            'rep.value' => 'required|numeric|exists:users,id',
            'mainValue' => 'required|numeric',
            'mainQty' => 'required|numeric',
            'products' => 'array',
            'brands' => 'array',
            'brands.*.value' => 'required|numeric|exists:brand,brand_id',
            'principals' => 'array',
            'principals.*.value' => 'required|numeric|exists:principal,principal_id',
            'products.*.value' => 'required|numeric|exists:product,product_id',
            'month'=>'required|date'
        ]);

        if ($validation->fails()) {
            throw new WebAPIException($validation->errors()->first());
        }

        $rep = $request->input('rep');
        $products = $request->input('products');
        $principals = $request->input('principals');
        $brands = $request->input('brands');
        $mainValue = $request->input('mainValue');
        $mainQty = $request->input('mainQty');

        $target = UserTarget::create([
            'u_id' => $rep['value'],
            'ut_value' => $mainValue,
            'ut_qty' => $mainQty,
            'ut_month'=>date('m',strtotime($request->input('month'))),
            'ut_year'=>date('Y',strtotime($request->input('month'))),
        ]);

        $productsByMR = Product::getByUser(User::find($rep['value']));

        try{

            DB::beginTransaction();

            $insertedProducts = [];

            if(isset($products)){
                $hasValue = false;
                foreach ($products as $key => $product) {
                    if(($product['valueTarget']&&$product['valueTarget']>0)||($product['qtyTarget']&&$product['qtyTarget']>0)){
                        $hasValue = true;
                        $insertedProducts[] = $product['value'];
                        UserProductTarget::create([
                            'ut_id' => $target->getKey(),
                            'product_id' => $product['value'],
                            'upt_value' => $product['valueTarget'],
                            'upt_qty' => $product['qtyTarget'],
                        ]);
                    }
                }
            }

            $insertedProducts = array_flip($insertedProducts);
            
            if(isset($brands)){
                foreach ($brands as $key => $brand) {
                    if(($brand['valueTarget']&&$brand['valueTarget']>0)||($brand['qtyTarget']&&$brand['qtyTarget']>0)){
                        UserProductTarget::create([
                            'ut_id' => $target->getKey(),
                            'brand_id' => $brand['value'],
                            'upt_value' => $brand['valueTarget'],
                            'upt_qty' => $brand['qtyTarget'],
                        ]);

                        $filteredProducts = $productsByMR->where('brand_id',$brand['value']);

                        foreach($filteredProducts as $product){
                            if(!isset($insertedProducts[$product->getKey()]))
                                UserProductTarget::create([
                                    'ut_id' => $target->getKey(),
                                    'product_id' => $product->getKey(),
                                    'upt_value' => ($brand['valueTarget'])?$brand['valueTarget']/$filteredProducts->count():0,
                                    'upt_qty' => $brand['qtyTarget']?$brand['qtyTarget']/$filteredProducts->count():0,
                                ]);
                        }
                    }
                }
            }

            if(isset($principals)){
                foreach ($principals as $key => $principal) {
                    if(($principal['valueTarget']&&$principal['valueTarget']>0)||($principal['qtyTarget']&&$principal['qtyTarget']>0)){
                        UserProductTarget::create([
                            'ut_id' => $target->getKey(),
                            'principal_id' => $principal['value'],
                            'upt_value' => $principal['valueTarget'],
                            'upt_qty' => $principal['qtyTarget'],
                        ]);

                        $filteredProducts = $productsByMR->where('principal_id',$principal['value']);

                        foreach($filteredProducts as $product){

                            if(!isset($insertedProducts[$product->getKey()]))
                                UserProductTarget::create([
                                    'ut_id' => $target->getKey(),
                                    'product_id' => $product->getKey(),
                                    'upt_value' => ($principal['valueTarget'])?$principal['valueTarget']/$filteredProducts->count():0,
                                    'upt_qty' => $principal['qtyTarget']?$principal['qtyTarget']/$filteredProducts->count():0,
                                ]);
                        }
                    }
                }
            }

            
            DB::commit();

        } catch (\Exception $e){

            DB::rollback();

            throw new WebAPIException("Server Error! ".$e->getMessage());
        }

        return response()->json([
            "success"=>true,
            "message"=>"Successfully updated the user's targets."
        ]);
    }
}
