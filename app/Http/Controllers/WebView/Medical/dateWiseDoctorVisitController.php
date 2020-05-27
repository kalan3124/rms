<?php
namespace App\Http\Controllers\WebView\Medical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Exceptions\WebViewException;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductiveVisit;
use Illuminate\Support\Facades\DB;

class dateWiseDoctorVisitController extends Controller {
     public function index(Request $request){
          $error = "";
          $validations = Validator::make($request->all(),[
               'date'=>'required|date'
           ]);
   
           if($validations->fails()){
               // throw new WebViewException("Can not validate your request.");
               $error = "Can not find any data";
           } else {
                $error = "";
           }
   
          $date = $request->input('date');

          $user = Auth::user();

          $doc_vists = DB::table('productive_visit AS pv')
          ->join('doctors AS d','d.doc_id','pv.doc_id')
          ->where('u_id',$user->getKey())
          ->whereDate('pro_start_time',$date)
          ->get();

          return view('WebView/Medical.date_wise_doctor_visit',[
               'doc_vists'=>$doc_vists,
               'error'=> $error
          ]);
     }
}
?>