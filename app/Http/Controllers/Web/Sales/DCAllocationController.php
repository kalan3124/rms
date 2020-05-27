<?php
namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\WebAPIException;
use App\Models\Chemist;
use App\Models\DcCustomer;
use Illuminate\Support\Facades\DB;

class DCAllocationController extends Controller {

    public function load (Request $request){

        // return $request->all();
        $validation = Validator::make($request->all(),[
            'user'=>'required|array',
            'user.value'=>'required|numeric'
       ]);

       if($validation->fails()){
            throw new WebAPIException("Invalid request");
       }

       $user_id = $request->input('user.value');


       $chemists = DcCustomer::where('u_id',$user_id)->with('chemists')->get();

       $chemists->transform(function($chemist){
           if(!$chemist->chemists)
           return null;

           return [
               'value'=>$chemist->chemists->chemist_id,
               'label'=>$chemist->chemists->chemist_name
            ];
        });
        // return $chemists;

       $chemists = $chemists->filter(function($chemist){
            return !!$chemist;
       })->values();

       return response()->json([
            'success'=>true,
            'chemist'=>$chemists
       ]);
    }

    public function save(Request $request){
        // return $request->all();
        $validation = Validator::make($request->all(),[
             'user'=>'required|array',
             'chemists'=>'required|array'
        ]);


        if($validation->fails()){
             throw new WebAPIException("We can not validate your request.");
        }

        $chemists = $request->input('chemists');
        $user = $request->input('user');

        foreach ($user as $key => $usr) {
             $us_id = DcCustomer::where('u_id',$usr['value'])->delete();

             foreach ($chemists as $key => $val) {
                DcCustomer::create([
                       'u_id' => $usr['value'],
                       'chemist_id' => $val['value'],
                  ]);
             }
        }

        return response()->json([
             'success'=>true,
             'message'=>"You have successfully allocated the given DC to given Chemist."
        ]);
  }
}
