<?php

namespace App\Http\Controllers\API\Sales\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Exceptions\SalesAPIException;
use App\Models\AndroidApp;
use Illuminate\Support\Facades\DB;
use App\Models\SfaSalesOrder;
use App\Models\SfaUserLogin;

class UserController extends Controller {
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
            throw new SalesAPIException("User not found!",1);
        }

        if(Auth::attempt(['user_name'=>$request->username,'password'=>$request->password])){
            $user = Auth::user();

            if(!in_array($user->getRoll(),config('shl.sales_app_privileged_user_types'))){
                // << SE2 >> \\
                throw new SalesAPIException("User Restricted!",2);
            } else {

                if($user->updated_at->format("Y-m-d")!=date("Y-m-d")){
                    $reportHash = preg_replace('/[^a-zA-Z0-9]/i',"",strtolower(base64_encode(microtime()."_".$user->getKey())));

                    $user->report_access_token = $reportHash;
                    $user->save();
                }

                /** LAST ORDER NO */
                $lastOrderNo = SfaSalesOrder::where('u_id',$user->getKey())->max('order_no');

                SfaUserLogin::create([
                    'u_id'=>$user->getKey(),
                    'login_date'=>date('Y-m-d H:i:s')
                ]);

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
                    'last_od_id'=>$lastOrderNo,
                    'versionCode'=>$this->getLatestApp()
                ]);
            }

        } else {
            // << SE3 >> \\
            throw new SalesAPIException("Password incorrect!",3);
        }
    }

    public function userDetails(Request $request){

        $user = Auth::user();

        if(!in_array($user->getRoll(),config('shl.sales_app_privileged_user_types'))){
             // Error ME2 
             throw new MediAPIException("User Restricted!",2);
        } else {

            $ItineraryApprovedStatus = false;
            $hasItinerary = false;
            
            try{
                $user->checkSfaItinerary();
                $ItineraryApprovedStatus = true;
            } catch (\Exception $e){
                $ItineraryApprovedStatus = false;
            }

            try{
                $user->checkSfaItinerary(false,false);
                $hasItinerary = true;
            } catch (\Exception $e) {
                $hasItinerary = false;
            }

            if($user->updated_at->format("Y-m-d")!=date("Y-m-d")){
                $reportHash = preg_replace('/[^a-zA-Z0-9]/i',"",strtolower(base64_encode(microtime()."_".$user->getKey())));

                $user->report_access_token = $reportHash;
                $user->save();
            }
            $lastOrderNo = SfaSalesOrder::where('u_id',$user->getKey())->max('order_no');
             return response()->json([
                  'result'=>true,
                  'token'=>$user->createToken('MediApp')->accessToken,
                  'name'=>$user->getName(),
                  'picture'=>$user->getBase64ProfilePicture(),
                  'phoneNumber'=>$user->getPhoneNumber(),
                  'email'=>$user->getEmail(),
                  'reportAccess'=>$user->report_access_token,
                  "Itinerary_Approved_Status"=>$ItineraryApprovedStatus,
                  "hasItinerary" => $hasItinerary,
                  'userId'=>$user->getKey(),
                  'last_od_id'=>$lastOrderNo,
                  'versionCode'=>$this->getLatestApp()
             ]);
        }
        
    }

    protected function getLatestApp(){
        /** @var AndroidApp $AndroidApp */
        $AndroidApp = AndroidApp::where('aa_v_type',2)
        ->latest()
        ->first();

        return (int) $AndroidApp->aa_v_name;
    }

}