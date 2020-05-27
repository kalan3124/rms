<?php

namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\DistributorSalesRep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class DsrAllocationController extends Controller {

    public function save(Request $request){
          $validation = Validator::make($request->all(),[
               'dsr'=>'required|array',
               'sr'=>'required|array'
          ]);

          if($validation->fails()){
               throw new WebAPIException("We can not validate your request.");
          }

          $dsr = $request->input('dsr');
          $distributors = $request->input('sr');
          
          foreach ($dsr as $key => $dsr) {
               $dis_id = DistributorSalesRep::where('sr_id',$dsr['value'])->get();

               DistributorSalesRep::whereIn('sr_id',$dis_id->pluck('sr_id')->all())->delete();

               foreach ($distributors as $key => $val) {
                    DistributorSalesRep::create([
                         'dis_id' => $val['value'],
                         'sr_id' => $dsr['value'],
                    ]);
               }
          }

          return response()->json([
               'success'=>true,
               'message'=>"You have successfully allocated the given DSR to given Distributors."
          ]);

    }

     public function load(Request $request){
          $validation = Validator::make($request->all(),[
               'dsr'=>'required|array',
               'dsr.value'=>'required|numeric'
          ]);

          if($validation->fails()){
               throw new WebAPIException("Invalid request");
          }

          $dsr_id = $request->input('dsr.value');

          $distributors = DistributorSalesRep::where('sr_id',$dsr_id)->with('distributor')->get();

          $distributors->transform(function($distributors){
               return [
                   'value'=>$distributors->distributor->id,
                   'label'=>$distributors->distributor->name
               ];
          }); 

          $distributors = $distributors->filter(function($distributor){
               return !!$distributor;
          })->values();

          return response()->json([
               'success'=>true,
               'dsr'=>$distributors
          ]);
     }
}