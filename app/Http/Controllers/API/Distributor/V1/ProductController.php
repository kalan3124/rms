<?php 
namespace App\Http\Controllers\API\Distributor\V1;

use App\CSV\DsrDistributor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\MediAPIException;
use Validator;
use App\Exceptions\SalesAPIException;
use App\Models\DistributorSalesRep;
use App\Models\DistributorStock;
use App\Models\DsrProduct;
use Illuminate\Support\Facades\DB;
use App\Models\PrincipalSequence;

class ProductController extends Controller
{
     /**
     * Assigned Principals
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function product_principals(Request $request){

        $user= Auth::user();

        $principal = DsrProduct::where('dsr_id',$user->getKey())->with('product','product.principal')->get();

        $principal->transform(function($val){
            return[
                'principal_id' => $val->product->principal->principal_id,
                'principal_code' => $val->product->principal->principal_code,
                'principal_name' => $val->product->principal->principal_name,
                'sequence_no' => 0,
            ];
        });

        return [
            'result'=>true,
            'principals_data'=>$principal,
            'count'=>$principal->count()
        ];
    }

    public function get_product_family(Request $request){

        $user = Auth::user();

        $product_family = DsrProduct::where('dsr_id',$user->getKey())->with('product','product.product_family')->get();

        $product_family->transform(function($val){
            return[
                'product_family_id' => $val->product->product_family->product_family_id,
                'product_family_code' => $val->product->product_family->product_family_code,
                'product_family_name' => $val->product->product_family->product_family_name
            ];
        });

        return [
            'result'=>true,
            'product_family_data'=>$product_family,
            'count'=>$product_family->count()
        ];

    }

    public function get_products(Request $request){

        $user = Auth::user();

        $products = DsrProduct::where('dsr_id',$user->getKey())->with('product','product.principal')->get();

        $distributors = DistributorSalesRep::where('dsr_id',$user->getKey())->get();


        $products->transform(function($val) use ($distributors) {

            $stock =  $distributors->map(function(DistributorSalesRep $distributorSalesRep) use($val) {
                return [
                    'disId'=>$distributorSalesRep->dis_id,
                    'stock'=> DistributorStock::checkStock($distributorSalesRep->dis_id,$val->product->product_id)
                ];
            });

            return[
                'principal_id' => $val->product->principal->principal_id,
                'product_family_id' => $val->product->product_family_id,
                'product_id' => $val->product->product_id,
                'product_code' => $val->product->product_code,
                'from_date' => '',
                'to_date' => '',
                'product_short_name' => $val->product->product_short_name,
                'product_name' => $val->product->product_name,
                'disStock'=>$stock
            ];
        });

        return [
            'result'=>true,
            'product_data'=>$products,
            'count'=>$products->count()
        ];
    }

    public function get_price_group(){

        $user = Auth::user();

        $priceGroup = DB::table('sales_price_list as spl')
        ->select(['spl.spl_id','spl.price_list_no','spl.description'])
        ->where('spl.deleted_at',NULL)
        ->where('spl.state','=','Active')
        ->groupBy('spl.price_list_no')
        ->get();

        $priceGroup->transform(function($pg){
            $product = DB::table('sales_price_list as spl')
            ->join('product as p','p.product_id','spl.product_id')
            ->select([
                'p.product_id',
                'spl.catalog_no',
                DB::raw('MAX(spl.base_price) as mrp'),
                DB::raw('MAX(spl.sales_prices_incl_tax) as sale_price')
                ])
            ->where('spl.price_list_no','=',$pg->price_list_no)
            ->where('spl.deleted_at',NULL)
            ->where('p.deleted_at',NULL)
            ->where('spl.state','=','Active')
            ->where('spl.valid_from_date','<=',date('Y-m-d h:i:s'))
            ->groupBy('spl.catalog_no')
            ->get();

            return [
                'priceG_Id'=>$pg->spl_id,
                'price_list_no'=>$pg->price_list_no,
                'priceG_Name'=>$pg->description,
                'items'=>$product
            ];
        });


        return [
            'result'=>true,
            'priceGroup'=>$priceGroup
        ];
    }

    public function get_product_batches(){

        $user = Auth::user();

        $distributors = DistributorSalesRep::where('sr_id',$user->getKey())->get();

        $dsrProducts = DsrProduct::with('product')->where('dsr_id',$user->getKey())->get();

        $stocks = DistributorStock::whereIn('product_id',$dsrProducts->pluck('product_id')->all())
            ->whereIn('dis_id',$distributors->pluck('dis_id')->all())
            ->with(['batch','product'])
            ->groupBy('db_id')
            ->select(['*',DB::raw('(SUM(ds_credit_qty) - SUM(ds_debit_qty)) AS stock')])
            ->get();

        $stocks->transform(function($stock){
            return [
                'product_id'=>$stock->product_id,
                'batch_id'=>$stock->db_id,
                'batch_code'=>$stock->batch?$stock->batch->db_code:"",
                'batch_stock'=>(int)$stock->stock,
                'batch_price'=>$stock->batch?$stock->batch->db_price:0.00,
                'batch_expiry'=>$stock->batch?$stock->batch->db_expire:0.00,
                'dis_id'=>$stock->dis_id
            ];
        });

        return [
            'result'=>true,
            'pro_batch_data'=>$stocks
        ];
    }

    public function addSeqToPrinciple(Request $request){

        $principles = json_decode($request['principles'],true);

        foreach ($principles as $key => $val) {

            $seq = PrincipalSequence::updateOrCreate([
                'principal_id' => $val['principleId']
            ]);
            
            $seq->sequence_no = $val['sequenceId'];
            $seq->save();
        }


        return [
            'result'=>true,
            'message'=>'Sequence Added'
        ];
    }
}