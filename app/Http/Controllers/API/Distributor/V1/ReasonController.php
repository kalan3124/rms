<?php 
namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use App\Models\Reason;

class ReasonController extends Controller{

    public function getUnproductiveReason(){

        $reasons = Reason::where('rsn_type',7)->get();

        $reasons->transform(function($rsn){
            return [
                'rsn_id'=>$rsn->rsn_id,
                'rsn_name'=>$rsn->rsn_name,
                'rsn_type'=>$rsn->rsn_type
            ];
        });

        return [
            'result'=>!$reasons->isEmpty(),
            'reasons'=>$reasons,
            'count'=>$reasons->count()
        ];
    }

    public function getReturnReason(){
        $reasons = Reason::where('rsn_type',8)->get();

        $reasons->transform(function($rsn){
            return [
                'rsn_id'=>$rsn->rsn_id,
                'rsn_name'=>$rsn->rsn_name,
                'rsn_type'=>$rsn->rsn_type
            ];
        });

        return [
            'result'=>!$reasons->isEmpty(),
            'reasons'=>$reasons,
            'count'=>$reasons->count()
        ];
    }


}