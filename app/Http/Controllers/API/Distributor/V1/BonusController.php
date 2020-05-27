<?php
namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Models\BonusDistributor;
use App\Models\BonusExclude;
use App\Models\BonusFreeProduct;
use App\Models\BonusProduct;
use App\Models\BonusRatio;
use App\Models\DistributorSalesRep;
use Illuminate\Support\Facades\Auth;

class BonusController extends Controller {
    public function schemes(){
        $user = Auth::user();

        /** @var DistributorSalesRep $distributors */
        $distributors = DistributorSalesRep::where('sr_id',$user->getKey())->get();

        $bonusSchemesForUser = BonusDistributor::where('dis_id',$distributors->pluck('dis_id')->all())->get();

        $bonusSchemes = Bonus::with([
            'products',
            'freeProducts',
            'ratios',
            'distributors',
            'excludes'
        ])->where(function($query) use($bonusSchemesForUser) {
            $query->orWhere('bns_all',1);
            $query->orWhereIn('bns_id',$bonusSchemesForUser->pluck('bns_id')->all());
        })
        ->whereDate('bns_start_date','<=',date('Y-m-d'))
        ->whereDate('bns_end_date','>=',date('Y-m-d'))
        ->get();

        $bonusSchemes->transform(function(Bonus $bonus) use($distributors) {
            return [
                'index'=>$bonus->getKey(),
                'label'=>$bonus->bns_code,
                'startDate'=> strtotime( $bonus->bns_start_date)*1000,
                'endDate'=> strtotime( $bonus->bns_end_date)*1000,
                'distributors'=>$bonus->bns_all?
                    $distributors->map(function(DistributorSalesRep $distributorSalesRep){
                        return [
                            'disId'=>$distributorSalesRep->dis_id
                        ];
                    })
                :
                    $bonus->distributors->map(function(BonusDistributor $bonusDistributor){
                        return [
                            'disId'=>$bonusDistributor->dis_id
                        ];
                    }),
                'ratios'=>$bonus->ratios->map(function(BonusRatio $ratio){
                    return [
                        'minQty'=>$ratio->bnsr_min,
                        'maxQty'=>$ratio->bnsr_max,
                        'numerator'=>$ratio->bnsr_purchase,
                        'denominator'=>$ratio->bnsr_free
                    ];
                }),
                'reqProducts'=>$bonus->products->unique('product_id')->values()->map(function(BonusProduct $bonusProduct){
                    return [
                        'itemId'=>$bonusProduct->product_id
                    ];
                }),
                'freeProducts'=>$bonus->freeProducts->unique('product_id')->values()->map(function(BonusFreeProduct $bonusFreeProduct){
                    return [
                        'itemId'=>$bonusFreeProduct->product_id
                    ];
                }),
                'excludeWhen'=>$bonus->excludes->map(function(BonusExclude $bonusExclude){
                    return [
                        'schemeId'=>$bonusExclude->bnse_bns_id
                    ];
                })
            ];
        });

        return response()->json([
            'result'=>true,
            'freeData'=>$bonusSchemes
        ]);
    }
}