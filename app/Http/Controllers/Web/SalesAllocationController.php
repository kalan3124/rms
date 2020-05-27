<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\Chemist;
use App\Models\Product;
use App\Models\SalesAllocation;
use App\Models\SalesAllocationMain;
use App\Models\SubTown;
use App\Models\Team;
use App\Models\TeamMemberPercentage;
use App\Models\TeamProduct;
use App\Models\TmpSalesAllocation;
use App\Models\UserCustomer;
use App\Traits\Territory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalesAllocationController extends Controller {

    use Territory;

    public function searchTowns(Request $request){
        $searchTerm = $request->input('searchTerm');
        $page = $request->input('page',1);
        $perPage = $request->input('perPage',10);


        $townsQuery = SubTown::where(function( Builder $query)use($searchTerm){
                $query->orWhere('sub_twn_name',"LIKE","%$searchTerm%");
                $query->orWhere('sub_twn_code',"LIKE","%$searchTerm%");
            });

        $count = $townsQuery->count();

        $towns = $townsQuery->take($perPage)->skip(($page-1)*$perPage)->get();


        $towns->transform(function($town){
            return [
                'name'=>$town->sub_twn_name,
                'id'=>$town->sub_twn_id,
                'code'=>$town->sub_twn_code,
                'town'=>$town->twn_name
            ];
        });

        $towns = $towns->values();

        return response()->json([
            'results'=>$towns,
            'count'=>$count
        ]);
    }

    public function searchProducts(Request $request){
        $searchTerm = $request->input('searchTerm');
        $page = $request->input('page',1);
        $perPage = $request->input('perPage',10);

        $team = $request->input('additional.team.value');

        $teamProducts = TeamProduct::where('tm_id',$team)->get();

        $productQuery = Product::where(function($query)use($searchTerm){
            $query->orWhere('product_code',"LIKE","%$searchTerm%");
            $query->orWhere('product_name',"LIKE","%$searchTerm%");
            $query->orWhere('product_short_name',"LIKE","%$searchTerm%");
        })->whereIn('product_id',$teamProducts->pluck('product_id'));

        $count = $productQuery->count();

        $products = $productQuery->take($perPage)->skip(($page-1)*$perPage)->get();


        $products->transform(function($product){
            return [
                'name'=>$product->product_name,
                'id'=>$product->product_id,
                'code'=>$product->product_code,
                'category'=>$product->principal?$product->principal->principal_name:""
            ];
        });

        $products = $products->values();

        return response()->json([
            'results'=>$products,
            'count'=>$count
        ]);
    }

    public function searchCustomers(Request $request){
        $searchTerm = $request->input('searchTerm');
        $page = $request->input('page',1);
        $perPage = $request->input('perPage',10);
        $additional = $request->input('additional',[]);

        $chemistQuery = Chemist::where(function(Builder $query)use($searchTerm){
            $query->orWhere('chemist_code',"LIKE","%$searchTerm%");
            $query->orWhere('chemist_name',"LIKE","%$searchTerm%");
        });

        if(isset($additional['towns'])&&isset($additional['mode'])){
            $towns = array_map(function($town){
                return $town['id'];
            },$additional['towns']);

            if($additional['mode']=='include'){
                $chemistQuery->whereIn('sub_twn_id',$towns);
            } else {
                $chemistQuery->whereNotIn('sub_twn_id',$towns);
            }
        }

        $count = $chemistQuery->count();

        $customers = $chemistQuery->take($perPage)->skip(($page-1)*$perPage)->get();

        $customers->transform(function($customer){
            return [
                'name'=>$customer->chemist_name,
                'id'=>$customer->chemist_id,
                'code'=>$customer->chemist_code,
                'town'=>$customer->sub_town?$customer->sub_town->sub_twn_name:""
            ];
        });

        $customers = $customers->values();

        return response()->json([
            'results'=>$customers,
            'count'=>$count
        ]);
    }

    public function fetchData(Request $request){
        $team = $request->input('team');

        $team = Team::with('teamUsers','teamUsers.user')->where('tm_id',$team['value'])->first();

        $members = $team->teamUsers->map(function($teamUser){
            if(!$teamUser->user)
                return null;

            return [
                'id'=>$teamUser->user?$teamUser->user->getKey():0,
                'name'=>$teamUser->user?$teamUser->user->name:"DELETED",
                'value'=>0
            ];
        });

        $members = $members->filter(function($member){return !!$member;})->values();

        $townAllocations = SalesAllocation::with(['town','town.town'])->where('tm_id',$team->getKey())->where('sa_ref_type',1)->get();
        $customerAllocations = SalesAllocation::with(['chemist','chemist.sub_town'])->where('tm_id',$team->getKey())->where('sa_ref_type',2)->get();
        $productAllocations = SalesAllocation::with(['product','product.principal'])->where('tm_id',$team->getKey())->where('sa_ref_type',3)->get();

        $modes = [];

        $productAllocation =  $productAllocations->first();
        if($productAllocation){
            $modes['products'] = $productAllocation->sa_ref_mode==1?"include":"exclude";
        } else {
            $modes['products'] = "include";
        }

        $customerAllocation =  $customerAllocations->first();
        if($customerAllocation){
            $modes['customers'] = $customerAllocation->sa_ref_mode==1?"include":"exclude";
        } else {
            $modes['customers'] = "include";
        }

        $townAllocation =  $townAllocations->first();
        if($townAllocation){
            $modes['towns'] = $townAllocation->sa_ref_mode==1?"include":"exclude";
        } else {
            $modes['towns'] = "include";
        }

        return response()->json([
            'modes'=>[
                'towns'=>'include',
                'products'=>'include',
                'customers'=>'include',
            ],
            'results'=>[
                'towns'=>[],
                'products'=>[],
                'customers'=>[]
            ],
            'members'=>$members,
        ]);
    }

    public function save(Request $request){
        $members = $request->input('members');
        $team = $request->input('team');
        $customerChecked = $request->input('selected.customers',[]);
        $productChecked = $request->input('selected.products',[]);
        $townChecked = $request->input('selected.towns',[]);

        $customerMode = $request->input('modes.customers');
        $productMode = $request->input('modes.products');
        $townMode = $request->input('modes.towns');

        $validation = Validator::make($request->all(),[
            'members'=>'required|array',
            'selected'=>'required|array',
            'modes'=>'required|array',
            'modes.products'=>'required',
            'modes.towns'=>'required',
            'team'=>'required|array',
            'team.value'=>'required|exists:teams,tm_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }
        
        try{

            DB::beginTransaction();

            $salesAllocationMain = SalesAllocationMain::create([
                'tm_id'=>$team['value']
            ]);
    
            foreach ($townChecked as $key => $town) {
                SalesAllocation::create([
                    'tm_id'=>$team['value'],
                    'sa_ref_type'=>1,
                    'sa_ref_id'=>$town['id'],
                    'sa_ref_mode'=>$townMode=='include'?1:0,
                    'sam_id'=>$salesAllocationMain->getKey()
                ]);
            }

            if($customerMode=='exclude'){
                $customerChecked = collect($customerChecked);
                $townChecked = collect($townChecked);

                $customerChecked = Chemist::whereNotIn('chemist_id',$customerChecked->pluck('id')->all())
                    ->whereIn('sub_twn_id',$townChecked->pluck('id'))
                    ->get();

                $customerChecked->transform(function($chemist){
                    return [
                        'id'=>$chemist->getKey()
                    ];
                });

            }

            foreach ($customerChecked as $key => $customer) {
                SalesAllocation::create([
                    'tm_id'=>$team['value'],
                    'sa_ref_type'=>2,
                    'sa_ref_id'=>$customer['id'],
                    'sa_ref_mode'=>1,
                    'sam_id'=>$salesAllocationMain->getKey()
                ]);
            }

            if($productMode=='exclude'){
                $productChecked = collect($productChecked);

                $productChecked = TeamProduct::where('tm_id',$team['value'])->whereNotIn('product_id',$productChecked->pluck('id'))->get();

                $productChecked->transform(function(TeamProduct $teamProduct ){
                    return [
                        'id'=>$teamProduct->product_id
                    ];
                });
            }
    
            foreach ($productChecked as $key => $product) {
                SalesAllocation::create([
                    'tm_id'=>$team['value'],
                    'sa_ref_type'=>3,
                    'sa_ref_id'=>$product['id'],
                    'sa_ref_mode'=>1,
                    'sam_id'=>$salesAllocationMain->getKey()
                ]);
            }
    
            foreach ($members as $key => $member) {
                $teamPercentage = TeamMemberPercentage::create([
                    'mp_percent'=> $member['value'],
                    'u_id'=>$member['id'],
                    'sam_id'=>$salesAllocationMain->getKey()
                ]);

            }

            foreach ($customerChecked as $key => $customer) {
                foreach ($productChecked as $key => $product) {
                    foreach ($members as $key => $member) {
                        TmpSalesAllocation::create([
                            'sam_id'=>$salesAllocationMain->getKey(),
                            'chemist_id'=>$customer['id'],
                            'product_id'=>$product['id'],
                            'u_id'=>$member['id'],
                            'tsa_percent'=>$member['value']
                        ]);
                    }
                }
            }

            DB::commit();
    
        } catch( \Exception $e){
            DB::rollBack();

            throw $e;

            throw new WebAPIException("Something went wrong in server. Please try again after few seconds.");
        }

        return response()->json([
            'success'=>true,
            'message'=>"Successfully allocated your team sale."
        ]);

    }
}