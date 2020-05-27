<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;

use Validator;

use App\Models\Team;
use App\Models\TeamUserProduct;
use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;

class ProductAllocationController extends Controller
{
    public function load(Request $request){

        $validator = Validator::make($request->all(),[
            'team'=>'required|array',
            'team.value'=>'required|numeric|exists:teams,tm_id'
        ]);

        if($validator->fails())
            throw new WebAPIException("Invalid team provided");
        
        // Finding the team
        $team = Team::with(['teamProducts','teamUsers','teamProducts.product','teamUsers.user'])->find($request->team['value']);

        // Getting only team user ids
        $teamUserIds = $team->teamUsers->pluck('tmu_id');

        // Getting user ptoducts for above team user ids
        $teamUserProducts = TeamUserProduct::with(['teamUser','teamUser.user'])->whereIn('tmu_id',$teamUserIds)->get();

        // Filtering unalloacted products
        $unallocatedProducts = clone $team->teamProducts;
        $newProducts = $unallocatedProducts->unique('product_id');

        // Transforming unallocated products
        $newProducts->transform(function($teamProduct){
            return [
                'value'=>$teamProduct->tmp_id,
                'label'=>isset($teamProduct->product->product_name)?$teamProduct->product->product_name:""
            ];
        });

        $teamUserProducts = $teamUserProducts->filter(function($teamUserProduct){ return $teamUserProduct->teamUser && $teamUserProduct->teamUser->user; })->values();

        // Filtering already allocated products
        $allocatedProducts = $teamUserProducts->all();

        $allocatedMapedProducts = [];


        foreach($team->teamUsers as $teamUser){
            $allocatedMapedProducts[$teamUser->tmu_id] = [];
        }

        if($teamUserProducts->isEmpty()){

            foreach ($allocatedMapedProducts as $tmu_id => $arr) {
                $teamProducts = $team->teamProducts->unique('product_id');
                foreach ($teamProducts as $teamProduct) {
                    $allocatedMapedProducts[$tmu_id][] = [
                        'value'=>$teamProduct->tmp_id,
                        'label'=>isset($teamProduct->product->product_name)?$teamProduct->product->product_name:""
                    ];
                }
            }
        }else {

            // Mapping allocated products with user id
            foreach ($allocatedProducts as $allocatedProduct) {
                $teamProduct = $team->teamProducts->where('tmp_id',$allocatedProduct->tmp_id)->first();

                if($teamProduct){
                    $allocatedMapedProducts[$allocatedProduct->tmu_id][] = [
                        'value'=>$allocatedProduct->tmp_id,
                        'label'=>isset($teamProduct->product->product_name)?$teamProduct->product->product_name:""
                    ];
                }
            }
        }

        $teamUsers = $team->teamUsers->filter(function($teamUser){
            return !!$teamUser->user;
        });

        $teamUsers = $teamUsers->transform(function($teamUser){
            return [
                'value'=>$teamUser->tmu_id,
                'label'=>$teamUser->user->name
            ];
        });



        return [
            'members'=>$teamUsers->values(),
            'allocated'=>$allocatedMapedProducts,
            'unallocated'=>array_values($newProducts->toArray())
        ];
    }


    public function save(Request $request){
        $validator = Validator::make($request->all(),[
            'team'=>'required|array',
            'team.value'=>'required|numeric|exists:teams,tm_id',
            'allocated'=>'required|array',
            'allocated.*.value'=>'numeric|exists:team_products,tmp_id'
        ]);

        if($validator->fails())
            throw new WebAPIException("Invalid data provided");

        $allocated = $request->input('allocated');

        $team = $request->input('team');

        $team = Team::with('teamUsers')->find($team['value']);

        $teamUserIds = $team->teamUsers->pluck('tmu_id');

        TeamUserProduct::whereIn('tmu_id',$teamUserIds)->delete();

        foreach ($allocated as $userId => $items) {
            foreach ($items as $item ) {
                $itemId = $item['value'];

                if($teamUserIds->contains($userId)){
                    TeamUserProduct::create([
                        'tmu_id'=>$userId,
                        'tmp_id'=>$itemId
                    ]);
                }
            }
        }

        return response()->json([
            'message'=>"Successfully saved your allocation. Force you representative to sync his andorid app."
        ]);
    }
}
