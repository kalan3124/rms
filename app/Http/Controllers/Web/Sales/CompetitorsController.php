<?php

namespace App\Http\Controllers\Web\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\WebAPIException;
use App\Models\Chemist;
use App\Models\CompetitorDetails;
use App\Models\CompetitorMarketSurvey;
use App\Models\Competitors;

class CompetitorsController extends Controller
{

    public function load(Request $request)
    {

        $data = CompetitorMarketSurvey::whereDate('survey_time', '>=', $request->from)->whereDate('survey_time', '<=', $request->to)->get();

        $data->transform(function ($val) {
            $chemist = Chemist::where('chemist_id', $val->chemist_id)->first();
            return [
                'id' => $val->com_survey_id,
                'chemist_name' => isset($chemist->chemist_name) ? $chemist->chemist_name : "",
                'con_1' => $val->contact_1,
                'con_2' => $val->contact_2,
                'email' => $val->email,
                'owner_name' => $val->owner_name,
                'remark' => $val->remark,
            ];
        });

        return [
            'results' => $data
        ];
    }

    public function loadEdit(Request $request)
    {

        $val = CompetitorMarketSurvey::where('com_survey_id', $request->id)->first();
        $chemist = Chemist::where('chemist_id', $val->chemist_id)->first();

        $data[] = [
            'id' => $val->com_survey_id,
            'chemist_name' => isset($chemist->chemist_name) ? $chemist->chemist_name : "",
            'con_1' => $val->contact_1,
            'con_2' => $val->contact_2,
            'email' => $val->email,
            'owner_name' => $val->owner_name,
            'noOfstuff' => $val->no_of_staff,
            'tot_pur_month' => $val->tot_pur_month,
            'pharmacy_pur_month' => $val->pharmacy_pur_month,
            'val_shl_pro_Redistributed' => $val->val_shl_pro_Redistributed,
            'val_shl_pro_thirdPartyDis' => $val->val_shl_pro_thirdPartyDis,
            'val_tot_pro_Redistributed' => $val->val_tot_pro_Redistributed,
            'pharmacy_sales_day' => $val->pharmacy_sales_day,
            'pharmacy_sales_month' => $val->pharmacy_sales_month,
            'survey_time' => $val->survey_time,
            'contact_person' => $val->contact_person,
            'from' => isset($val->from)?$val->from:"--",
            'to' => isset($val->to)?$val->to:"--"
        ];


        $compts = CompetitorDetails::where('com_survey_id',$request->id)->get();

        $compts->transform(function($val){
            $cmp = Competitors::where('cmp_id',$val->cmp_id)->first();
            return[
                'cmp_name' => $cmp->cmp_name,
                'total_purchase_value' => $val->total_purchase_value,
                'visit_frequency' => $val->visit_frequency,
                'visit_day_Of_week' => $val->visit_day_Of_week,
            ];
        });

        return [
            'data' => $data,
            'comp' => $compts
        ];
    }

    public function dateEdit(Request $request)
    {
        // return $request->all();
        $val = CompetitorMarketSurvey::where('com_survey_id', $request->id)->first();

        $val->from = $request->from;
        $val->to = $request->to;
        $val->save();

        return [
            'status' => true,
            'msg' => "Valid date has been changed"
        ];
    }
}
