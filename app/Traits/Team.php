<?php
namespace App\Traits;

use App\Models\Team as TeamModel;
use App\Models\TeamUser;
use App\Exceptions\MediAPIException;
use App\Models\DsrProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
/**
 * Team based common functions
 */
trait Team {
    /**
     * Returning products by user
     *
     * @param User $user
     * @param array $with
     * @param callback $func
     * @return Collection
     */
    public function getProductsByUser($user,$with=[],$func=null){
        $productIds = [];

        // Sending all team products to field manager
        if($user->getRoll()==config("shl.field_manager_type")){
            $team = TeamModel::with('teamProducts')->where('fm_id',$user->getKey())->latest()->first();

            if(!$team)
                throw new MediAPIException("Field manager does not in any team",28);

            foreach($team->teamProducts as $teamProduct){
                $productIds[] = $teamProduct->product_id;
            }
        } else if($user->getRoll()==config('shl.distributor_sales_rep_type')){
            $products = DsrProduct::where('dsr_id',$user->getKey())->get();
            $productIds = $products->pluck('product_id')->all();
        } else {
            $teamUser = TeamUser::with('team','teamUserProducts','teamUserProducts.teamProduct','teamUserProducts.teamProduct')->where('u_id',$user->getKey())->latest()->first();

            if(!$teamUser||!$teamUser->team)
                throw new MediAPIException("Medical representaive does not in any team",21);


            if(!$teamUser->teamUserProducts->isEmpty()){
                // Sending allocated products if allocated

                foreach ($teamUser->teamUserProducts as $teamUserProduct) {

                    if($teamUserProduct&&$teamUserProduct->teamProduct){
                        $productIds[] = $teamUserProduct->teamProduct->product_id;
                    }
                }
            } else {
                $teamUsers = TeamUser::with(['teamUserProducts'])->where('tm_id',$teamUser->tm_id)->get();

                $allocated = false;

                foreach($teamUsers as $otherTeamUser){
                    if(!$otherTeamUser->teamUserProducts->isEmpty()) $allocated=true;
                }

                if($allocated){
                    // If products allocated to one user in the team
                    $productIds=[];
                } else {
                    // If not allocated to any member
                    $team = TeamModel::with('teamProducts')->where('tm_id',$teamUser->tm_id)->latest()->first();

                    if(!$team)
                        throw new MediAPIException("Field manager does not in any team",28);

                    foreach($team->teamProducts as $teamProduct){
                        $productIds[] = $teamProduct->product_id;
                    }
                }
            }
            
        }

        $query = Product::whereIn('product_id',$productIds)->with($with);

        if($func){
            $query->where($func);
        }

        return $query->get();
    }
}