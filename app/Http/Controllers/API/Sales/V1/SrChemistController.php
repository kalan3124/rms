<?php
namespace App\Http\Controllers\API\Sales\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\MediAPIException;
use Validator;
use App\Exceptions\SalesAPIException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\SrChemist;
use App\Models\Chemist;

class SrChemistController extends Controller{

     public function srChemistData(Request $request){

          Storage::put('/public/expenses.txt', json_encode($request->all()));

          $user= Auth::user();
          // Checking the request is empty
          if(!$request->has('jsonString'))
               throw new MediAPIException('Some parameters are not found', 5);

          // Decoding the json
          $json_decode = json_decode($request['jsonString'],true);

          $validator = Validator::make($json_decode, [
               'outlet_name' => 'required',
               'address' => 'required',
               'lat' => 'required',
               'lon' => 'required',
               'image_file' => 'required',
               'update_status' => 'required',
               'chem_code' => 'required'
           ]);
   
           // Throw an exception if required parameters not supplied
           if ($validator->fails()) 
               throw new MediAPIException($validator->errors()->first(), 4);

          $msg = "";

          $imgUrl = null;
          if(isset($json_decode['image_file'])){
               // Bese 64 image conversion
               $image = $json_decode['image_file'];
               $image = str_replace('data:image/png;base64,', '', $image);
               $image = str_replace(' ', '+', $image);
               $imageName = str_random(10).'.'.'png';
               Storage::put('/public/srChemistImage/'.date("Y").'/'.date("m").'/'.date("d").'/'.$imageName,base64_decode($image));

               $imgUrl = '/storage/srChemistImage/'.date("Y").'/'.date("m").'/'.date("d").'/'.$imageName;
          }

          if($json_decode['update_status'] == true){

               $sr_chemsit_update = Chemist::where('chemist_code',$json_decode['chem_code'])->first();
               $sr_chemsit_update->mobile_number = $json_decode['mobile'];
               $sr_chemsit_update->email = $json_decode['email'];
               $sr_chemsit_update->lat = $json_decode['lat'];
               $sr_chemsit_update->lon = $json_decode['lon'];
               $sr_chemsit_update->image_url = $imgUrl;
               $sr_chemsit_update->updated_u_id = $user->getKey();
               $sr_chemsit_update->phone_no = $json_decode['telephone'];
               $sr_chemsit_update->chemist_owner = $json_decode['owner_name'];
               $sr_chemsit_update->save();

               $msg = "Sr Chemsit Data has been successfully Updated";
               
          } elseif($json_decode['update_status'] == false){

               $sr_chemsit = SrChemist::create([
                    'chem_name' => $json_decode['outlet_name'],
                    'owner_name' => $json_decode['owner_name'],
                    'address' => $json_decode['address'],
                    'mobile number' => $json_decode['mobile'],
                    'email' => $json_decode['email'],
                    'lat' => $json_decode['lat'],
                    'lon' => $json_decode['lon'],
                    'image_url' => $imgUrl,
                    'update_status' => $json_decode['update_status'],
                    'added_by' => $user->getKey(),
                    'created_u_id' => $user->getKey()
               ]);
               $msg = "Sr Chemist Data has been successfully entered";
          }

          return response()->json([
               "result" => true,
               "message" => $msg
          ]);

     }
}
?>