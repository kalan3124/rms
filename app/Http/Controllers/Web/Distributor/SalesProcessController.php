<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Jobs\SalesDataProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SalesProcessController extends Controller {
    public function submit(Request $request){
        $month  = strtotime( $request->input('month'));

        $user = Auth::user();

        $content = json_encode([
            'status'=>'running',
            'message'=>'Started processing your request!',
            'percentage'=>0
        ]);

        $name = '/sales_data_progress/'.$user->getKey().'.json';

        Storage::put($name,$content);

        SalesDataProcess::dispatch($user->getKey(),date('Y',$month),date('m',$month))->onConnection('sync');

        return response()->json([
            "success"=>true,
            "message"=>"Started your process!"
        ]);

    }

    /**
     * Checking the status of process
     * 
     */
    public function checkProgress(){
        $user = Auth::user();

        $jsonString = Storage::get('/sales_data_progress/'.$user->getKey().'.json');

        if(! $jsonString)
            return response()->json([
                'status'=>'running',
                'percentage'=>0,
                "message"=>"Started"
            ]);

        $result = json_decode($jsonString,true);

        return response()->json($result);
    }
}