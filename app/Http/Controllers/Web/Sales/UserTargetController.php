<?php 
namespace App\Http\Controllers\Web\Sales;

use App\Exceptions\WebAPIException;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\ProductLatestPriceInformation;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;
use App\Models\User;
use App\Traits\SRAllocation;

class UserTargetController extends Controller
{
    use SRAllocation;

    public function load(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'rep' => 'required',
            'rep.value' => 'required|numeric|exists:users,id',
            'month'=>'required|date'
        ]);

        if ($validation->fails()) {
            throw new WebAPIException("Can not validate your request. Please provide a sales represenative");
        }

        $rep = $request->input('rep');
        $user = User::find($rep['value']);
        $today = strtotime($request->input('month'));

        $mainTarget = SfaTarget::where('u_id', $rep['value'])
            ->where('trg_month',date('m',strtotime($request->input('month'))))
            ->where('trg_year',date('Y',strtotime($request->input('month'))))
            ->latest()
            ->first();

        if ($mainTarget) {
            $productTargets = SfaTargetProduct::where('sfa_trg_id', $mainTarget->getKey())->get();
        }else {
            $productTargets = collect([]);
        }

        $userProducts = $this->getProductsBySR($user,$today);
        if($userProducts->isEmpty())
            throw new WebAPIException("Can not find any product to selected user. Please provide a valid date");

            $products = [];
            foreach ($userProducts as $product) {
                if (!isset($products[$product['product_id']])) {

                $productTarget = $productTargets->firstWhere('product_id', $product['product_id']);
                $price = 0;

                $latestPrice = ProductLatestPriceInformation::where('product_id',$product['product_id'])->latest()->first();
                if(isset($latestPrice)){                    
                    if(isset($latestPrice->lpi_bdgt_sales))
                        $price = $latestPrice->lpi_bdgt_sales;
                    else
                        $price = $latestPrice->lpi_pg01_sales;
                }

                $products[$product['product_id']] = [
                    "label" => '['.$product['catalog_no'].'] '.shortString($product['product_name'],30),
                    "value" => $product['product_id'],
                    'price'=>$price??0.00,
                    "valueTarget" => $productTarget ? $productTarget->stp_amount : "0.00",
                    'qtyTarget' => $productTarget ? $productTarget->stp_qty : 0,
                ];
                }
            }    

            return [
                "products" => $products,
            ];
    }

    public function save(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'rep' => 'required|array',
            'rep.value' => 'required|numeric|exists:users,id',
            'products' => 'array',
            'products.*.value' => 'required|numeric|exists:product,product_id',
            'month'=>'required|date'
        ]);

        if ($validation->fails()) {
            throw new WebAPIException($validation->errors()->first());
        }

        $rep = $request->input('rep');
        $products = $request->input('products');

        $srCode = User::find($rep['value']);
        $srAreaCode = substr($srCode->u_code,0,4);

        $area = Area::where('ar_code',$srAreaCode)->first();

        $targets = SfaTarget::where('u_id',$rep['value'])->where('trg_year',date('Y',strtotime($request->input('month'))))->where('trg_month',date('m',strtotime($request->input('month'))))->latest()->first();

        if($targets){
            SfaTarget::where('sfa_trg_id',$targets['sfa_trg_id'])->delete();
        }
        
        try{

            $target = SfaTarget::create([
                'u_id' => $rep['value'],
                'ar_id'=> $area->ar_id,
                'trg_year'=>date('Y',strtotime($request->input('month'))),
                'trg_month'=>date('m',strtotime($request->input('month')))
            ]);

            if(isset($products)){
                $hasValue = false;
                foreach ($products as $key => $product) {
                    if(($product['valueTarget']&&$product['valueTarget']>0)||($product['qtyTarget']&&$product['qtyTarget']>0)){
                        $hasValue = true;
                        SfaTargetProduct::create([
                            'sfa_trg_id' => $target->getKey(),
                            'product_id' => $product['value'],
                            'budget_price' => $product['price'],
                            'stp_qty' => $product['qtyTarget'],
                            'stp_amount' => $product['valueTarget']
                        ]);
                    }
                }
            }
                DB::commit();

        }catch (\Exception $e){

            DB::rollback();

            throw new WebAPIException("Server Error! ".$e->getMessage());
        }

        return response()->json([
            "success"=>true,
            "message"=>"Successfully updated the user's targets."
        ]);
    }
}