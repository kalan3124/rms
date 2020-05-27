<?php
namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Exceptions\DisAPIException;
use App\Models\DistributorSalesOrder;
use App\Models\DistributorSalesRep;

class DistributorController extends Controller{
     public function getDistributors(Request $request){
          $user = Auth::user();

          $distributors = DistributorSalesRep::where('sr_id',$user->getKey())->with('distributor')->get();
          // return $distributors;

          $distributors->transform(function($val) use($user){
               $last_order = DistributorSalesOrder::where('u_id',$user->getKey())->where('dis_id',$val->dis_id)->max('order_no');
               
               return[
                    "dis_id"=> $val->dis_id,
                    "dis_name"=> $val->distributor->name,
                    "dis_last_od_id"=> isset($last_order)?$last_order:null,
                    "dis_last_re_id"=> null
               ];
          });

          return[
               'result' => true,
               'distributors' => $distributors
          ];
     }
}