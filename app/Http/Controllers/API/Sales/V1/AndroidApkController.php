<?php

namespace App\Http\Controllers\API\Sales\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\MediAPIException;
use Validator; 
use App\Models\AndroidApp;

class AndroidApkController extends Controller{

    public function checkVersion(Request $request) {

        // Make a new validation rule
        $validator = Validator::make($request->all(), [
            'apk_type' => 'required',
            'apk_version' => 'required'
        ]);
        // Throw an exception if required parameters not supplied
        if ($validator->fails()) 
            throw new MediAPIException($validator->errors()->first(), 4);

        $date = date("Y-m-d H:i:s");

        $data = [
            'aa_v_type'=>$request->apk_type
        ];
        $AndroidApp = AndroidApp::where($data)
        ->where('aa_v_name','>',(int) $request->apk_version)
        ->whereDate('aa_start_time','<=',$date)
        ->latest()
        ->first();
        
        if(!$AndroidApp){
            throw new MediAPIException("Can not found latest version",24);
        }else{
            return response()->json([
                "result" => true,
                "url" => url('/storage/app/'.$AndroidApp->aa_url)
            ]);
        }
        
    }

}