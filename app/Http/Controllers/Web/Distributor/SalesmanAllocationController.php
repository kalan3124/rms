<?php 
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\DistributorSalesMan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class SalesmanAllocationController extends Controller {

    public function load(Request $request){
        $validation = Validator::make($request->all(),[
             'dsr'=>'required|array',
             'dsr.value'=>'required|numeric'
        ]);

        if($validation->fails()){
             throw new WebAPIException("Invalid request");
        }

        $dsr_id = $request->input('dsr.value');

        $distributors = DistributorSalesMan::where('sr_id',$dsr_id)->with('distributor')->get();

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
            $dis_id = DistributorSalesMan::where('sr_id',$dsr['value'])->get();

            DistributorSalesMan::whereIn('sr_id',$dis_id->pluck('sr_id')->all())->delete();

            foreach ($distributors as $key => $val) {
                DistributorSalesMan::create([
                    'dis_id' => $val['value'],
                    'sr_id' => $dsr['value'],
                ]);
            }
        }

        return response()->json([
            'success'=>true,
            'message'=>"You have successfully allocated the given Salesman to given Distributors."
        ]);

    }
}