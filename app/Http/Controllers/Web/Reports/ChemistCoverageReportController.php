<?php
namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Validator;
use App\Exceptions\WebAPIException;
use App\Models\Chemist;
use App\Models\User;
use App\Models\Itinerary;
use App\Models\ItineraryDate;
use App\Models\ProductiveVisit;

use App\Traits\Territory;

class ChemistCoverageReportController extends ReportController
{
    use Territory;

    protected $updateColumnsOnSearch = true;

    protected $title = "Chemist Coverage Monitoring Report";

    public function search(Request $request){
        $validation = Validator::make($request->all(),[
            'values'=>'required|array',
            'values.user.value'=>'required',
        ]);

        if($validation->fails()){
            throw new WebAPIException($validation->errors()->first());
        }

        $chemistQuery = Chemist::with('chemist_class','sub_town');

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
            $chemistQuery->whereIn('sub_twn_id',$subTwnIds);
        }

        $user = collect();
        if($request->has('values.user.value')){
            $user = User::find($request->input('values.user.value'));
            // filture by mr allocated areas
            $getAllocatedTerritories = $this->getAllocatedTerritories($user);
            $chemistQuery->whereIn('sub_twn_id',$getAllocatedTerritories->pluck('sub_twn_id')->all());
        }

        $dateOfItienary = $this->itienaryDates($user,$year,$month);
        $joinFieldWork = $this->joinFieldWorkers($user,$year,$month);

        $chemist = $chemistQuery->get();
        $grand_total = [];
        $chemist->transform(function($chem,$key)use($dateOfItienary,$year,$month,$joinFieldWork,&$grand_total){
            $return =[
                'town_code'=>$chem->sub_town['sub_twn_code'],
                'town_name'=>$chem->sub_town['sub_twn_name'],
                'chemist_name'=>$chem->chemist_name,
                'chemist_code'=>$chem->chemist_code,
                'chemist_class_name'=>$chem->chemist_class['chemist_class_name'],
                's_no'=>$key + 1
            ];
            $index = 0;
            $lineChemTotal = 0;
            for($a = 0; $a < sizeof($dateOfItienary);$a++){
                $new_date = $year[0].'-'.$month[0].'-'.$dateOfItienary[$a];
                $visitStatus = ProductiveVisit::where('chemist_id','=',$chem->chemist_id)
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
                    $lineChemTotal++;
                }
                if(isset($grand_total[$index])){
                    $grand_total[$index] += $return['date_'.$a];
                }else{
                    $grand_total[$index] = $return['date_'.$a];
                }
                $index++;
            }
            $return['total']=number_format($lineChemTotal,2);
            return $return;
        });

        $total = [
            'province_name'=>'Page Total',
            'town_code'=>'',
            'town_name'=>'',
            'chemist_name'=>'',
            'chemist_code'=>'',
            'chemist_class_name'=>'',
            'master_twn_code'=>'',
            'master_twn_name'=>'',
            's_no'=>'',
            'special'=>true
        ];

        for($a = 0; $a < sizeof($dateOfItienary);$a++){
            $total['date_'.$a] = $grand_total[$a];
        }
        $total['total'] = number_format($chemist->sum('total'),2);

        $chemist->push($total);

        return [
            'count'=>0,
            'results'=>$chemist
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

        $dynamicColom = 10 + (sizeof($dateOfItienary) * 2);

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
                    "title"=>"Town"
                ],
                [
                    "title"=>"Name of Chemist"
                ],
                [
                    "title"=>"Chemist Code"
                ],
                [
                    "title"=>"Class"
                ],
                [
                    "title"=>"Master Town Code"
                ],
                [
                    "title"=>"Master Town Name"
                ],
                [
                    "title"=>"S. No."
                ]
        ];

        $i = 8;
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
        $j = 8 + (sizeof($dateOfItienary) * 2);
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
        $columnController->text('chemist_name')->setLabel("");
        $columnController->text('chemist_code')->setLabel("");
        $columnController->text('chemist_class_name')->setLabel("");
        $columnController->text('master_twn_code')->setLabel("");
        $columnController->text('master_twn_name')->setLabel("");
        $columnController->text('s_no')->setLabel("Date");
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
        $inputController->ajax_dropdown('team')->setLabel('Team')->setLink('team')->setValidations('');
        $inputController->ajax_dropdown('divi_id')->setLabel('Division')->setLink('division')->setValidations('');
        $inputController->ajax_dropdown('user')->setWhere([
                "tm_id" => "{team}",
                "divi_id" => "{divi_id}",
                'u_tp_id'=>"2|3".'|'.config('shl.product_specialist_type'),
            ])
            ->setLabel('PS/MR or FM')
            ->setLink('user')->setValidations('');
        $inputController->ajax_dropdown('ar_id')->setLabel('Area')->setLink('area')->setValidations('');
        $inputController->date("month")->setLabel('Month');
        $inputController->setStructure([
            'ar_id','divi_id',
            ['team','user','month']
        ]);
    }

}
