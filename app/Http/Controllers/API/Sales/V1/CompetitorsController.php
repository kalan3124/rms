<?php
namespace App\Http\Controllers\API\Sales\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Exceptions\SalesAPIException;
use App\Models\CompetitorDetails;
use App\Models\CompetitorMarketSurvey;
use App\Models\Competitors;
use App\Models\SalesmanValidCustomer;
use Illuminate\Support\Facades\DB;

class CompetitorsController extends Controller{

    public function loadCompetitors(Request $request){
        $comp = Competitors::query();
        $results = $comp->get();

        $results->transform(function($val){
            return[
                "competitorId" => $val->cmp_id,
                "competitorName" => $val->cmp_name
            ];
        });

        return [
            "result" =>  true,
            "data" => $results
        ];
    }

    public function saveDetails(Request $request){

        if(!$request->has('jsonString'))
            throw new SalesAPIException('Some parameters are not found', 5);

        $json_decode = json_decode($request['jsonString'],true);

        $timestamp = $json_decode['surveyTime'] / 1000;

        // Formating the unix timestamp to a string
        $surveyTime = date("Y-m-d h:i:s", $timestamp);

        try {
            DB::beginTransaction();

            $comp = CompetitorMarketSurvey::create([
                'chemist_id' => $json_decode['chemId'],
                'survey_time' => $surveyTime,
                'lat' => $json_decode['latitude'],
                'lon' => $json_decode['longitude'],
                'battery' => $json_decode['batteryLevel'],
                'owner_name' => $json_decode['ownerName'],
                'contact_person' => $json_decode['contactPerson'],
                'contact_1' => isset($json_decode['contactNumber1'])?$json_decode['contactNumber1']:NUll,
                'contact_2' => isset($json_decode['contactNumber2'])?$json_decode['contactNumber2']:NULL,
                'email' => $json_decode['email'],
                'no_of_staff' => $json_decode['numberOfStaff'],
                'tot_pur_month' => $json_decode['totalPurchasesMonth'],
                'pharmacy_pur_month' => $json_decode['pharmaceuticalPurchaseMonth'],
                'val_shl_pro_thirdPartyDis' => $json_decode['valueShlProductThirdPartyDis'],
                'val_tot_pro_Redistributed' => $json_decode['valueTotalProductRedistributed'],
                'val_shl_pro_Redistributed' => $json_decode['valueShlProductsRedistributed'],
                'pharmacy_sales_day' => $json_decode['pharmacySalesDay'],
                'pharmacy_sales_month' => $json_decode['pharmacySalesMonth'],
                'remark' => $json_decode['remark'],
                'activeStatus' => $json_decode['activeStatus']
            ]);

            foreach ($json_decode['competitorDetail'] as $key => $value) {
               CompetitorDetails::create([
                    'com_survey_id' => $comp->getKey(),
                    'cmp_id' => $value['competitorId'],
                    'total_purchase_value' => $value['totalpurchaseValue'],
                    'visit_frequency' => $value['visitFrequency'],
                    'visit_day_Of_week' => $value['visitDayOfWeek']
               ]);
            }



            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            "result" => true,
            "message" => "Competitors market survey details successfully Saved"
        ]);
    }

    public function loadValid(Request $request){
        $user = Auth::user();

        $chemists = SalesmanValidCustomer::where('u_id', $user->getKey())->whereDate('from_date', '<=', date('Y-m-01'))->whereDate('to_date', '>=', date('Y-m-t'))->get();

        $chemists->transform(function($val){
            return[
                "startTime" => 1588564800000,
                "endTime" => 1590811200000,
                // "startTime" => strtotime($val->from)*1000,
                // "endTime" => strtotime($val->to)*1000,
                "ChemId" => $val->chemist_id
            ];
        });

        return [
            "result" =>  true,
            "data" => $chemists
        ];
    }

}
