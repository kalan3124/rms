<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Validator;
use App\Exceptions\WebAPIException;
use App\Models\Doctor;
use App\Models\DoctorSubTown;
use App\Models\Area;
use App\Models\User;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\ProductiveVisit;

use App\Traits\Territory;

class DoctorCoverageReportController extends ReportController
{
    use Territory;

    protected $updateColumnsOnSearch = true;

    protected $title = "Doctor Coverage Monitoring Report";

    public function search(Request $request){
        $validation = Validator::make($request->all(),[
            'values'=>'required|array',
            'values.user.value'=>'required',
        ]);

        if($validation->fails()){
            throw new WebAPIException("MR/PS or FM field is required!");
        }

        $doctorQuery = DoctorSubTown::with('doctor','subTown','doctor.doctor_speciality');

        $year = [date('Y',strtotime($request->input('values.month')))];
        $month = [date('m',strtotime($request->input('values.month')))];

        if($request->has('values.ar_id.value')){
            $areas = DB::table('area AS a')
            ->join('town AS t','t.ar_id','=','a.ar_id')
            ->join('sub_town AS st','st.twn_id','=','t.twn_id')
            ->whereNull('a.deleted_at')
            ->whereNull('t.deleted_at')
            ->whereNull('st.deleted_at')
            ->where('a.ar_id','=',$request->input('values.ar_id.value'))
            ->select(['st.sub_twn_id'])
            ->groupBy('st.sub_twn_id')->get();
            $subTwnIds = $areas->pluck('sub_twn_id')->all();
            $doctorQuery->whereIn('sub_twn_id',$subTwnIds);
        }

        $user = collect();
        if($request->has('values.user.value')){
            $user = User::find($request->input('values.user.value'));
            // filture by mr allocated areas
            $getAllocatedTerritories = $this->getAllocatedTerritories($user);
            $doctorQuery->whereIn('sub_twn_id',$getAllocatedTerritories->pluck('sub_twn_id')->all());
        }

        $dateOfItienary = $this->itienaryDates($user,$year,$month);
        $joinFieldWork = $this->joinFieldWorkers($user,$year,$month);

        $doctor = $doctorQuery->get();
        $grand_total = [];
        $doctor->transform(function($dc, $key)use($dateOfItienary,$year,$month,$joinFieldWork,&$grand_total){

            $visits_count = ProductiveVisit::where('doc_id',$dc->doctor->doc_id)->whereMonth('pro_start_time',$month)->count();
            $return = [
                'province_name'=>'',
                'town_code'=>$dc->subTown['sub_twn_code'],
                'town_name'=>$dc->subTown['sub_twn_name'],
                'specialty'=>$dc->doctor->doctor_speciality['speciality_name'],
                'doc_code'=>$dc->doctor->doc_code,
                'doc_name'=>$dc->doctor->doc_name,
                's_no'=>$key + 1,
                'no_of_visits_per_month' => $visits_count
            ];

            $lineDocTotal = 0;
            $index = 0;
            for($a = 0; $a < sizeof($dateOfItienary);$a++){
                $new_date = $year[0].'-'.$month[0].'-'.$dateOfItienary[$a];
                $visitStatus = ProductiveVisit::where('doc_id','=',$dc->doctor->doc_id)
                ->whereDate('pro_start_time',$new_date)
                ->count();

                if($joinFieldWork[$a]!=''){
                    $joinField = User::where('id',$joinFieldWork[$a])->get();
                    $joinFieldUser = [$joinField->pluck('name')];
                } else {
                    $joinFieldUser = '';
                }
                $return['date_'.$a]=($visitStatus>0)?1:0;
                $return['work_with_'.$a]= $joinFieldUser;
                if($visitStatus>0){
                $lineDocTotal++;
                }
                if(isset($grand_total[$index])){
                    $grand_total[$index] += $return['date_'.$a];
                }else{
                    $grand_total[$index] = $return['date_'.$a];
                }
                $index++;
            }
            $return['total']=number_format($lineDocTotal,2);
            return $return;
        });

        $total = [
            'province_name'=>'Page Total',
            'town_code'=>'',
            'town_name'=>'',
            'specialty'=>'',
            'doc_code'=>'',
            'doc_name'=>'',
            's_no'=>'',
            'no_of_visits_per_month' => '',
            'special'=>true
        ];

        for($a = 0; $a < sizeof($dateOfItienary);$a++){
            $total['date_'.$a] = $grand_total[$a];
        }
        $total['total'] = number_format($doctor->sum('total'),2);

        $doctor->push($total);

        return [
            'count'=>0,
            'results'=>$doctor
        ];

    }
    protected function itienaryDates ($user,$year,$month){
        $itienary [] = Itinerary::where(function($query)use($user){
            $query->orWhere('rep_id',$user->getKey());
            $query->orWhere('fm_id',$user->getKey());
        })
        ->whereNotNull('i_aprvd_at')
        ->where('i_year',$year)
        ->where('i_month',$month)
        ->latest()
        ->first();

        $itienary = collect( $itienary);

        $itineraryDates = ItineraryDate::whereIn('i_id',$itienary->pluck('i_id')->all())
                    ->get();
        return $itineraryDates->pluck('id_date');
    }

    protected function joinFieldWorkers($user,$year,$month){
        $itienary [] = Itinerary::where(function($query)use($user){
            $query->orWhere('rep_id',$user->getKey());
            $query->orWhere('fm_id',$user->getKey());
        })
        ->whereNotNull('i_aprvd_at')
        ->where('i_year',$year)
        ->where('i_month',$month)
        ->latest()
        ->first();

        $itienary = collect( $itienary);

        $joinField = ItineraryDate::whereIn('i_id',$itienary->pluck('i_id')->all())
                    ->get();
        return $joinField->pluck('u_id');
    }

    protected function getAdditionalHeaders($request){
        $dynamicColom = 0;
        $yearMonth = "";
        $dateOfItienary = [];
        if($request->has('values.user.value')){
            $user = User::find($request->input('values.user.value'));
            $year = date('Y',strtotime($request->input('values.month')));
            $month = date('m',strtotime($request->input('values.month')));
            $yearMonth = $year.'-'.date("F", mktime(0, 0, 0, $month, 1));
            $dateOfItienary = $this->itienaryDates($user,$year,$month);
        }

        $dynamicColom = 11 + (sizeof($dateOfItienary) * 2);

        $first_row = [
            [
                "title"=> $yearMonth,
                "colSpan"=> $dynamicColom
            ]
        ];

        $second_row = [
                [
                    'title'=>"Province"
                ],
                [
                    "title"=>"Town Code"
                ],
                [
                    "title"=>"Town Name"
                ],
                [
                    "title"=>"Specialty"
                ],
                [
                    "title"=>"Name of Doctor"
                ],
                [
                    "title"=>"Doctor Code"
                ],
                [
                    "title"=>"Master Town Code"
                ],
                [
                    "title"=>"Master Town Name"
                ],
                [
                    "title"=>"S. No."
                ],
                [
                    "title"=>"No of Visits per month"
                ]
        ];

        $i = 9;
        $day_count = 0;
        for($a = 0; $a < (sizeof($dateOfItienary) * 2);$a++){
            $i++;
            $ar = array();
            if($a % 2 == 0){
                $day_count++;
                $ar['title'] = $day_count;
            }else{
                $ar['title'] = 'work with';
            }
            $second_row[$i]=$ar;
        }
        $j = 9 + (sizeof($dateOfItienary) * 2);
        for($i = 0; $i < 1; $i++){
            $j++;
            $arr = array();
            $arr['title'] = 'Total';
            $second_row[$j]= $arr;
        }

        $columns = [
            $first_row,
            $second_row
        ];
        return $columns;
    }

    public function setColumns($columnController,Request $request){
        $columnController->text('province_name')->setLabel("");
        $columnController->text('town_code')->setLabel("");
        $columnController->text('town_name')->setLabel("");
        $columnController->text('specialty')->setLabel("");
        $columnController->text('doc_name')->setLabel("");
        $columnController->text('doc_code')->setLabel("");
        $columnController->text('master_twn_code')->setLabel("");
        $columnController->text('master_twn_name')->setLabel("");
        $columnController->text('s_no')->setLabel("Date");
        $columnController->text('no_of_visits_per_month')->setLabel("");
        if($request->has('values.user.value')){
        $user = User::find($request->input('values.user.value'));
        $year = [date('Y',strtotime($request->input('values.month')))];
        $month = [date('m',strtotime($request->input('values.month')))];
        $dateOfItienary = $this->itienaryDates($user,$year,$month);
        for ($i = 0; $i < sizeof($dateOfItienary); $i++){
            $columnController->text('date_'.$i)->setLabel($dateOfItienary[$i]);
            $columnController->text('work_with_'.$i)->setLabel("");
        }
        }
        $columnController->number('total')->setLabel('Total');
    }

    protected function setInputs($inputController){
        $inputController->ajax_dropdown('divi_id')->setLabel('Division')->setLink('division')->setValidations('');
        $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team')->setValidations('');
        $inputController->ajax_dropdown('user')->setWhere(["tm_id" => "{team}","divi_id" => "{divi_id}",'u_tp_id'=>"2|3".'|'.config('shl.product_specialist_type')])->setLabel('PS/MR or FM')->setLink('user')->setValidations('');
        $inputController->ajax_dropdown('ar_id')->setLabel('Area')->setLink('area')->setValidations('');
        $inputController->date("month")->setLabel('Month');
        $inputController->setStructure([
            ['ar_id','divi_id'],
            ['team','user','month']
        ]);
    }


}
