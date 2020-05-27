<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\DistributorSalesRep;
use App\Models\Site;
use App\Models\DistributorSite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class SiteAllocationController extends Controller {

     public function loadSite(Request $request){
          $site =  $request->input('site');

          $sites = Site::query();

          if(isset($site)){
               $sites->where('site_name','LIKE','%'.$site.'%');
          }

          $results = $sites->get();

          $results->transform(function($val){
               return[
                    'label' => $val->site_name,
                    'value' => $val->site_id
               ];
          });

          return $results;
     }

     public function save(Request $request){
          $validation = Validator::make($request->all(),[
               'dsr'=>'required|array',
               'site'=>'required|array'
          ]);

          if($validation->fails()){
               throw new WebAPIException("We can not validate your request.");
          }

          $dsr = $request->input('dsr');
          $sites = $request->input('site');
          
          foreach ($dsr as $key => $dsr) {
               $dis_id = DistributorSite::where('dis_id',$dsr['value'])->delete();

               foreach ($sites as $key => $val) {
                    DistributorSite::create([
                         'site_id' => $val['value'],
                         'dis_id' => $dsr['value'],
                    ]);
               }
          }

          return response()->json([
               'success'=>true,
               'message'=>"You have successfully allocated the given DSR to given Sites."
          ]);

    }

     public function load(Request $request){
          $validation = Validator::make($request->all(),[
               'site'=>'required|array',
               'site.value'=>'required|numeric'
          ]);

          if($validation->fails()){
               throw new WebAPIException("Invalid request");
          }

          $site_id = $request->input('site.value');

          $distributors = DistributorSite::where('site_id',$site_id)->with('distributor')->get();

          $distributors->transform(function($distributor){
               if(!$distributor->distributor)
                    return null;
                    
               return [
                    'value'=>$distributor->distributor->id,
                    'label'=>$distributor->distributor->name
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
?>