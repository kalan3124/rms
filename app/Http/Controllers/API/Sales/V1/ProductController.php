<?php 
namespace App\Http\Controllers\API\Sales\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\MediAPIException;
use Validator;
use App\Exceptions\SalesAPIException;
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

        $principal = DB::table('salesman_valid_parts as sp')
        ->join('product as p','p.product_id','sp.product_id')
        ->join('principal as pr', 'p.principal_id','pr.principal_id')
        ->select([
            'pr.principal_id',
            'pr.principal_code',
            'pr.principal_name'
        ])
        ->where('sp.deleted_at',NULL)
        ->where('p.deleted_at',NULL)
        ->where('pr.deleted_at',NULL)
        ->where('sp.u_id',$user->getKey())
        ->groupBy('pr.principal_id')
        ->get();

        $principal->transform(function($val){

            $seq = PrincipalSequence::where('principal_id',$val->principal_id)->first();

            return[
                "principal_id"=> $val->principal_id,
                "principal_code"=> $val->principal_code,
                "principal_name"=> $val->principal_name,
                "sequence_no" => isset($seq->sequence_no)?$seq->sequence_no:0
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

        $product_family = DB::table('salesman_valid_parts as sp')
        ->join('product as p','p.product_id','sp.product_id')
        ->join('product_family as pf','pf.product_family_id','p.product_family_id')
        ->select([
            'pf.product_family_id',
            'pf.product_family_code',
            'pf.product_family_name'
        ])
        ->where('sp.deleted_at',NULL)
        ->where('p.deleted_at',NULL)
        ->where('pf.deleted_at',NULL)
        ->where('sp.u_id',$user->getKey())
        ->groupBy('pf.product_family_id')
        ->get();

        return [
            'result'=>true,
            'product_family_data'=>$product_family,
            'count'=>$product_family->count()
        ];
    }

    public function get_products(Request $request){

        $user = Auth::user();

        $product = DB::table('salesman_valid_parts as sp')
        ->join('product as p','p.product_id','sp.product_id')
        ->select([
            'p.principal_id',
            'p.product_family_id',
            'p.product_id',
            'p.product_code',
            'sp.from_date',
            'sp.to_date',
            DB::Raw('IFNULL(p.product_short_name,"") AS product_short_name'),
            'p.product_name'
        ])
        ->where('sp.deleted_at',NULL)
        ->where('p.deleted_at',NULL)
        ->where('sp.u_id',$user->getKey())
        ->whereDate('sp.from_date','<=',date('Y-m-d'))
        ->whereDate('sp.to_date','>=',date('Y-m-d'))
        ->groupBy('p.product_id')
        ->get();

        return [
            'result'=>true,
            'product_data'=>$product,
            'count'=>$product->count()
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

        // $priceGroup = DB::table('ext_sales_price_list_uiv as spl')
        // ->select(['spl.s_price_id AS spl_id','spl.price_list_no','spl.description'])
        // ->where('spl.deleted_at',NULL)
        // ->where('spl.state','=','Active')
        // ->groupBy('spl.price_list_no')
        // ->get();

        // $priceGroup->transform(function($pg){
        //     $product = DB::table('ext_sales_price_list_uiv as spl')
        //     ->join('product as p','p.product_code','spl.catalog_no')
        //     ->select([
        //         'p.product_id',
        //         'spl.catalog_no',
        //         DB::raw('MAX(spl.base_price) as mrp'),
        //         DB::raw('MAX(spl.sales_prices_incl_tax) as sale_price')
        //         ])
        //     ->where('spl.price_list_no','=',$pg->price_list_no)
        //     ->where('spl.deleted_at',NULL)
        //     ->where('p.deleted_at',NULL)
        //     ->where('spl.state','=','Active')
        //     ->where('spl.valid_from_date','<=',date('Y-m-d h:i:s'))
        //     ->groupBy('spl.catalog_no')
        //     ->get();

        //     return [
        //         'priceG_Id'=>$pg->spl_id,
        //         'price_list_no'=>$pg->price_list_no,
        //         'priceG_Name'=>$pg->description,
        //         'items'=>$product
        //     ];
        // });

        return [
            'result'=>true,
            'priceGroup'=>$priceGroup
        ];
    }

    public function get_product_batches(){

        $user = Auth::user();

        $productBatches = DB::table('invent_part_in_stock AS ips')
        ->join('product AS p','ips.product_id','p.product_id')
        ->join('salesman_valid_parts AS svp',function($join)
        {
            $join->on('svp.product_id', '=', 'p.product_id');
            $join->on('svp.contract', '=', 'ips.contract');
        })
        ->select([
            'p.product_id',
            'ips.w_d_r_no',
            DB::raw('SUM(ips.available_qty) AS available_qty'),
            DB::raw('0 AS batch_price'),
            DB::raw("DATE_FORMAT(ips.expiration_date,'%Y-%m-%d') AS expiration_date")
        ])
        ->where('svp.u_id','=',$user->getKey())
        ->where('ips.available_qty', '>' ,0)
        ->whereNull('ips.deleted_at')
        ->whereNull('p.deleted_at')
        ->whereNull('svp.deleted_at')
        ->groupBy('p.product_id','ips.w_d_r_no')
        ->get();

        $productBatches->transform(function($pro){
            return [
                'product_id'=>$pro->product_id,
                'batch_id'=>0,
                'batch_code'=>$pro->w_d_r_no,
                'batch_stock'=>(int)$pro->available_qty,
                'batch_price'=>$pro->batch_price,
                'batch_expiry'=>$pro->expiration_date?$pro->expiration_date:""
            ];
        });

        return [
            'result'=>true,
            'pro_batch_data'=>$productBatches
        ];
    }

    public function addSeqToPrinciple(Request $request){

        $user = Auth::user();

        $principles = json_decode($request['jsonString'],true);

        foreach ($principles['principles'] as $key => $val) {

            $seq = PrincipalSequence::updateOrCreate([
                'principal_id' => $val['principleId']
            ]);
            
            $seq->sequence_no = $val['sequenceId'];
            $seq->u_id = $user->getKey();
            $seq->save();
        }


        return [
            'result'=>true,
            'message'=>'Sequence Added'
        ];
    }
}