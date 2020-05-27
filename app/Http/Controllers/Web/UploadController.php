<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    public function save(Request $request, $type){
        if(!in_array($type,['app','csv','image'])){
            throw new WebAPIException("Invalid file type got");
        }

        $forceExtensions = [
            'app'=>'apk',
            'csv'=>'csv'
        ];

        $time = time();

        $random = rand(1000,2000);

        $token = md5($time.$random);

        if(isset($forceExtensions[$type]))
            $extension = $forceExtensions[$type];
        else
            $extension = $request->file('file')->extension();

        $request->file('file')->storeAs('public/'.$type,$token.'.'.$extension);

        return response()->json([
            'success'=>true,
            'token'=>$token.'.'.$extension
        ]);
    } 
}
