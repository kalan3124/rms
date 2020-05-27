<?php
namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Exceptions\DisAPIException;

class UserController extends Controller{
      /**
     * Login a user
     *
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
          $validator = Validator::make($request->all(),['username'=>'required|exists:users,user_name','password'=>'required']);

          
          if($validator->fails()){
               // << SE1 >> \\
               throw new DisAPIException("User not found!",1);
          }
          if(Auth::attempt(['user_name'=>$request->username,'password'=>$request->password])){
               $user = Auth::user();
               
               if($user->getRoll() != config('shl.distributor_sales_rep_type')){
                    // << SE2 >> \\
                    throw new DisAPIException("User Restricted!",2);
               } else {
                    if($user->updated_at->format("Y-m-d")!=date("Y-m-d")){
                         $reportHash = preg_replace('/[^a-zA-Z0-9]/i',"",strtolower(base64_encode(microtime()."_".$user->getKey())));

                         $user->report_access_token = $reportHash;
                         $user->save();
                    }

                    return response()->json([
                         'result'=>true,
                         'token'=>$user->createToken('MediApp')->accessToken,
                         'name'=>$user->getName(),
                         'picture'=>$user->getBase64ProfilePicture(),
                         'phoneNumber'=>$user->getPhoneNumber(),
                         'email'=>$user->getEmail(),
                         'reportAccess'=>$user->report_access_token,
                         "hasItinerary"=>true,
                         'userId'=>$user->getKey(),
                         'last_od_id'=>null
                    ]);
               }
          } else { 
                // << SE3 >> \\
               throw new DisAPIException("Password incorrect!",3);
          }
    }
}

?>