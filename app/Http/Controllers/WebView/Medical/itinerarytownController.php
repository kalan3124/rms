<?php
namespace App\Http\Controllers\WebView\Medical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use  App\Models\User;
use App\Traits\Territory;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SubTown;
use App\Form\Columns\Date;

class itinerarytownController extends Controller {

     use Territory;
     public function index(Request $request){

          $data = "";

          $mr = Auth::user(); 

               $begin = new \DateTime(date('Y-m-01'));
               $end = new \DateTime(date("Y-m-t"));
               $end = $end->modify('1 day'); 

               $interval = new \DateInterval('P1D');
               $daterange = new \DatePeriod($begin, $interval ,$end);
               
               $result = [];

               $subTowns = collect([]);
               $show = "";

               foreach ($daterange as $key => $date) {
                    try {
                         $subTownsToday = $this->getTerritoriesByItinerary($mr,strtotime($date->format('Y-m-d')));
                    } catch (\Throwable $exception) {
                         $subTownsToday = collect();
                    }

                    $itinerarySubTowns = [];
               
                    if($subTownsToday->isEmpty()){
                         $itinerarySubTowns = [];
                    }
                    else {
                         $itinerarySubTowns = $subTownsToday->pluck('sub_twn_id')->all();
                    }

                    $subTowns = SubTown::whereIn('sub_twn_id',$itinerarySubTowns)->select('*')->get();

                    $subTowns->transform(function($subTown){
                         if(isset($subTown)){
                              return $subTown->sub_twn_name;
                         }
                    });
                    $subTownNames = implode('/ ',$subTowns->all());

                    if($subTownNames){
                         $show =  $subTownNames;
                    } else {
                         $show = 'No Itinerary for Day';
                    }

                    $result[] = [
                         "date"=>$date->format('Y-m-d'),
                         "ar_name"=>$show
                    ];
               }

               
               // return $result;
          return view('WebView/Medical.itinerary_town',['mrUser'=>$result]);
     }
}
?>