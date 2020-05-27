<?php
namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Web\Reports\ReportController;

use Illuminate\Http\Request;
use App\Form\Columns\ColumnController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\DistributorStock;
use App\Models\Principal;
use App\Models\Product;
use App\Models\User;

class DoctorProfileReportController extends ReportController{
    protected $title = "Doctor Profile Report";
    protected $updateColumnsOnSearch = true;

    public function search( Request $request){
        $validations = Validator::make($request->all(),[
            'values'=>'required|array',
            'values.s_date'=>'required|date'
        ]);

        switch ($sortBy) {
            default:
                $sortBy = 'date';
                break;
        }

        $values = $request->input('values');

        $formatedresulst = [];

        $productive = DB::table('productive_visit AS p')
                 ->select(
                     'doc.doc_name',
                     'u.u_code as sr_code',
                     'u.name as sr_name',
                     'promo.promo_name as promo',
                     'vt.vt_name as vt_name',
                     'p.promo_remark',
                     'p.pro_summary',
                     'p.pro_visit_no',
                     'p.lat',
                     'p.lon',
                     DB::raw('DATE(p.pro_start_time) as date'))
                 ->join('users AS u','p.u_id','u.id')
                 ->leftJoin('doctors AS doc','p.doc_id','doc.doc_id')
                 ->leftJoin('promotion AS promo','p.promo_id','promo.promo_id')
                 ->leftJoin('visit_type AS vt','p.visited_place','vt.vt_id')
                 ->where('u.id','!=',2)
                 ->where('u.id','!=',5)
                 ->whereDate('p.pro_start_time',">=",date("Y-m-d",strtotime($values['s_date'])))
                 ->whereDate('p.pro_start_time',"<=",date("Y-m-d",strtotime($values['e_date'])));

        // return $productive->get(); //633

        $unproductive = DB::table('unproductive_visit AS un')
                ->select(
                'doc.doc_name',
                'u.u_code as sr_code',
                'u.name as sr_name',
                're.rsn_name as reason',
                'rety.rsn_type as reason_type',
                'vt.vt_name as vt_name',
                'un.un_visit_no',
                'un.lat',
                'un.lon',
                DB::raw('DATE(un.unpro_time) as date'))
                ->join('users AS u','un.u_id','u.id')
                ->join('doctors AS doc','un.doc_id','doc.doc_id')
                ->leftJoin('reason AS re','un.reason_id','re.rsn_id')
                ->leftJoin('reason_types AS rety','re.rsn_type','rety.rsn_tp_id')
                ->leftJoin('visit_type AS vt','un.visited_place','vt.vt_id')
                ->where('u.id','!=',2)
                ->where('u.id','!=',5)
                ->whereDate('un.unpro_time',">=",date("Y-m-d",strtotime($values['s_date'])))
                ->whereDate('un.unpro_time',"<=",date("Y-m-d",strtotime($values['e_date'])));

        // return $unproductive->get(); //3

        if (isset($values['doctor_id'])) {
            $productive->where('doc.doc_id',$request->input('values.doctor_id.value'));
            $unproductive->where('doc.doc_id',$request->input('values.doctor_id.value'));
        }

        if (isset($values['sr'])) {
            $productive->where('p.u_id',$request->input('values.sr.value'));
            $unproductive->where('un.u_id',$request->input('values.sr.value'));
        }

        $productive_ = $productive->get();
        $unproductive_ = $unproductive->get();

        $result = $productive_->concat($unproductive_);

        // return $result;

        $result->transform(function($val){
            $description = '';
            if(isset($val->vt_name))
                $description =$description.' '. $val->vt_name;

            if(isset($val->promo))
                $description =$description. ' | '.$val->promo;

            if(isset($val->pro_summary))
                $description =$description. ' | '.$val->pro_summary;

            if(isset($val->reason_type))
                $description =$description.' '. $val->reason_type;

            if(isset($val->reason))
                $description =$description. ' | '.$val->reason;


            $map = $val->lat.','.$val->lon;

            return [
                'doc_name'=>$val->doc_name,
                // 'city'=>null,
                'sr_code'=>$val->sr_code,
                'sr_name'=>$val->sr_name,
                'type'=>isset($val->reason)?'Unproductive ('.$val->un_visit_no.')':'Productive ('.$val->pro_visit_no.')',
                'date'=>$val->date,
                'ac_description'=>$description,
                'remark'=>isset($val->promo_remark)?$val->promo_remark:'',
                'view_map' => [
                    'label' => 'Location',
                    'link' => 'https://www.google.com/maps/search/?api=1&query='.$map,
                ],
            ];
        });

        // return $result;

        return[
            'count'=>0,
            'results'=> $result
        ];

    }

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('doc_name')->setLabel("Doctor Name");
        // $columnController->text('city')->setLabel("City");
        $columnController->text('sr_code')->setLabel("SR Code");
        $columnController->text('sr_name')->setLabel("SR Name");
        $columnController->text('type')->setLabel("Productive / Unproductive");
        $columnController->text('date')->setLabel("Date");
        $columnController->text('ac_description')->setLabel("Activity Description");
        $columnController->text('remark')->setLabel("Remark");
        $columnController->link('view_map')->setDisplayLabel("Location")->setLabel("Location");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown("doctor_id")->setLabel("Doctor")->setLink("doctor")->setValidations('');
        $inputController->ajax_dropdown("sr")->setLabel("SR")->setLink("user")->setWhere(['u_tp_id' => config('shl.medical_rep_type')])->setValidations('');
        // $inputController->ajax_dropdown('city')->setLabel('City')->setLink('sub_town')->setValidations('');
        $inputController->date("s_date")->setLabel("From");
        $inputController->date("e_date")->setLabel("To");

        $inputController->setStructure([
            ["doctor_id","sr"],
            ["s_date","e_date"]
        ]);
    }
}
