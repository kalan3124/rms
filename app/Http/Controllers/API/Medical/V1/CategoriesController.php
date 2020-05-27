<?php 
namespace App\Http\Controllers\API\Medical\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reason;
use App\Models\VisitType;

class CategoriesController extends controller{

    public function all(){

        $reasons = Reason::whereIn('rsn_type',[
            config('shl.unproductive_reason_type'),
            config('shl.sampling_reason_type'),
            config('shl.detailing_reason_type'),
            config('shl.promotion_reason_type'),
            config('shl.expenses_reason_type'),
            config('shl.bata_reason_type'),
        ])->get();


        $reasons->transform(function($reason){
            return [
                'type'=>$reason->rsn_type,
                'id'=>$reason->getKey(),
                'reason'=>$reason->rsn_name
            ];
        });

        return [
            'result'=>!$reasons->isEmpty(),
            'reasons'=>$reasons,
            'count'=>$reasons->count()
        ];
    }


    public function visitTypes(){
        $visitTypes = VisitType::all();

        $visitTypes->transform(function($visitType){
            return [
                'id'=>$visitType->getKey(),
                'type'=>$visitType->vt_name
            ];
        });

        return [
            'result'=>!$visitTypes->isEmpty(),
            'types'=>$visitTypes,
            'count'=>$visitTypes->count()
        ];
    }
}