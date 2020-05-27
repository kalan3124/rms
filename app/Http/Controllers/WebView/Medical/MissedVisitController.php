<?php
namespace App\Http\Controllers\WebView\Medical;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Traits\Territory;
use Carbon\CarbonPeriod;

use App\Models\UserCustomer;
use App\Models\SubTown;
use App\Models\Chemist;
use App\Models\Doctor;
use App\Models\ProductiveVisit;
use App\Models\UnproductiveVisit;
use App\Models\DoctorSubTown;
use App\Models\OtherHospitalStaff;

class MissedVisitController extends controller
{
    use Territory;
    public function index(Request $request){
        return view('WebView/Medical.missed_visits');
    }

    public function search(Request $request){

        $user = Auth::user();
        //get date period
        $period = CarbonPeriod::create($request->input('fdate'), $request->input('tdate'));

        $result = [];

        foreach ($period as $date) {
            try {
                $itineraryTowns = $this->getTerritoriesByItinerary($user,strtotime($date->format('Y-m-d')));
            } catch (\Throwable $exception) {
                $itineraryTowns = collect();
            }

            $itinerarySubTownIds = [];

            if($itineraryTowns->isEmpty()){
                $itinerarySubTownIds = [];
            }
            else {
                $itinerarySubTownIds = $itineraryTowns->pluck('sub_twn_id');
            }

            // Getting chemists for above time ids
            $chemists = Chemist::with('sub_town')->whereIn('sub_twn_id',$itinerarySubTownIds)->get();

            //Getting missed chemist
            $unproductiveVisit = UnproductiveVisit::with('chemist','chemist.sub_town')
            ->whereDate('unpro_time','=',$date->format('Y-m-d'))
            ->where('u_id',$user->getKey())
            ->get();

            //get missed chemist 
            $missedChemist = $unproductiveVisit->whereIn('chemist_id',$chemists->pluck('chemist_id')->all());

            $missedChemist->transform(function($msChem){
                return [
                    'doc_chem_id'=>$msChem->chemist_id,
                    'doc_chem_name'=>$msChem->chemist->chemist_name,
                    'doc_chem_type'=>1, //Chemist
                    'speciality'=>$msChem->chemist->sub_town->sub_twn_name
                ];
            });

            // Getting doctors for today
            $doctors = DB::table('doctor_intitution AS ti')
            ->join('institutions AS i','i.ins_id','=','ti.ins_id','inner')
            ->whereIn('i.sub_twn_id',$itinerarySubTownIds)
            ->where([
                'i.deleted_at'=>null,
                'ti.deleted_at'=>null
            ])
            ->select('ti.doc_id')
            ->groupBy('ti.doc_id')
            ->get();
            //getting doctors from subtown assignment
            $doctorsBySubTown = DoctorSubTown::whereIn('sub_twn_id',$itinerarySubTownIds)->get();
            //merge subtown doctors with institute assigned doctors
            $doctors = $doctors->merge($doctorsBySubTown);

            //get missed doctors
            $missedDoctors = $unproductiveVisit->whereIn('doc_id',$doctors->pluck('doc_id')->all());

            $missedDoctors->transform(function($msDoc){
                return [
                    'doc_chem_id'=>$msDoc->doc_id,
                    'doc_chem_name'=>$msDoc->doctor->doc_name,
                    'doc_chem_type'=>0, //Doctor,
                    'speciality'=>'speciality : '.$msDoc->doctor->doctor_speciality->speciality_name
                ];
            });

            // Getting Other Hospital Staff
            $otherStaff = OtherHospitalStaff::whereIn('sub_twn_id',$itinerarySubTownIds)->get();
            //get other hospital staff
            $missedOtherHos = $unproductiveVisit->whereIn('hos_stf_id',$otherStaff->pluck('hos_stf_id')->all());

            $missedOtherHos->transform(function($msHos){
                return [
                    'doc_chem_id'=>$msHos->hos_stf_id,
                    'doc_chem_name'=>$msHos->other_hos_staff->hos_stf_name,
                    'doc_chem_type'=>2, //Other Hos Staff
                    'speciality'=>''
                ];
            });

            $filturedDocChemHos = $missedChemist->concat($missedDoctors)->concat($missedOtherHos);
            $filturedDocChemHos->all();

            if(!$filturedDocChemHos->isEmpty())
                $result[] = [
                    "date"=>$date->format('Y-m-d'),
                    "missedVisits"=>$filturedDocChemHos
                ];
        }
        return view('WebView/Medical.missed_visit_details',['result'=>$result]);
    }
}