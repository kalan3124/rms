<?php

namespace App\Http\Controllers\Web\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Form\Columns\ColumnController;
use App\Models\InvoiceLine;
use App\Models\Product;
// use App\Models\Team;
use App\Traits\Territory;
use App\Traits\Team;
use App\Models\TeamUser;
use App\Models\User;
use App\Models\UserTarget;
use App\Models\UserProductTarget;

class MrtargetAchivementReportController extends ReportController
{
    use Team, Territory;
    protected $title = "MR Target Vs Achievement Report";

    public function search(Request $request)
    {
        $values = $request->input('values');

        $query = DB::table('team_users as tu')
            ->select('tu.tm_id', 'u.name', 'u.id', 'tm_name', 'u.u_code', 'u.divi_id')
            ->join('users as u', 'u.id', 'tu.u_id')
            ->join('teams as t', 't.tm_id', 'tu.tm_id')
            ->whereNull('u.deleted_at')
            ->whereNull('t.deleted_at')
            ->whereNull('tu.deleted_at');

        if (isset($values['divi_name'])) {
            $query->where('u.divi_id', $values['divi_name']['value']);
        }

        if (isset($values['user_id'])) {
            $query->where('u.id', $values['user_id']['value']);
        }

        if (isset($values['team_name'])) {
            $query->where('t.tm_id', $values['team_name']['value']);
        }

        $count = $this->paginateAndCount($query, $request, 't.tm_id');
        $results = $query->get();

        $formatedresulst = [];
        $tot_cur_ach_val = 0;

        $finat_year_month = date('Y') . '-' . date('m-01', strtotime($values['month']));
        $next_year = date('Y-m-d', strtotime($finat_year_month . '+1 year -1 day'));
        // return date('Y-m-t',strtotime($finat_year_month));
        foreach ($results as $key => $row) {

            $user = User::find($row->id);
            $teamUser = TeamUser::where('u_id', $user->getKey())->latest()->first();
            $products = Product::getByUserForSales($user, ['latestPriceInfo']);

            $towns = $this->getAllocatedTerritories($user);
            $currentMonthAchievement = $this->makeQuery($towns, date('Y-m-01', strtotime($finat_year_month)), date('Y-m-t', strtotime($finat_year_month)), $teamUser ? $teamUser->tm_id : 0, $teamUser ? $teamUser->u_id : 0);
            $YeaMonthsAchi = $this->makeQuery($towns, date('Y-01-01', strtotime($values['month'])), date('Y-m-t', strtotime($values['month'])), $teamUser ? $teamUser->tm_id : 0, $teamUser ? $teamUser->u_id : 0);

            $pro_user_target = 0;
            $user_currentMonthAchi = 0;
            $user_ytd_currentMonthAchi = 0;
            foreach ($products as $key => $product) {
                $user_target = $this->getTargetsForMonth($row->id, $product->product_id, $product->brand_id, $product->principal_id, date('Y', strtotime($values['month'])), date('m', strtotime($values['month'])));
                $pro_user_target += $user_target->upt_value;

                $currentMonthAchievementProduct = $currentMonthAchievement->where('product_id', $product->product_id)->first();
                $user_currentMonthAchi += isset($currentMonthAchievementProduct) ? $currentMonthAchievementProduct['amount'] : 0;

                $ytdAchi = $YeaMonthsAchi->where('product_id', $product->product_id)->first();
                $user_ytd_currentMonthAchi += isset($ytdAchi) ? $ytdAchi['amount'] : 0;
            }

            if (($user_currentMonthAchi != 0 && $pro_user_target != 0) || ($user_ytd_currentMonthAchi != 0 && $pro_user_target != 0)) {
                $achi_prasant = ($user_currentMonthAchi / $pro_user_target) * 100;
                $ytd_value_av = ($user_ytd_currentMonthAchi / $pro_user_target) * 100;
            } else {
                $achi_prasant = 0;
                $ytd_value_av = 0;
            }

            $formatedresulst[] = [
                'emp_no' => $row->u_code,
                'name' => $row->name,
                'orig_target_val' => $pro_user_target ? number_format($pro_user_target, 2) : "0.00",
                'orig_target_val_new' => $pro_user_target ? $pro_user_target : "0.00",
                'month_ach_val' => $user_currentMonthAchi ? number_format($user_currentMonthAchi, 2) : "0.00",
                'month_ach_val_new' => $user_currentMonthAchi ? $user_currentMonthAchi : "0.00",
                'ach_%' => isset($achi_prasant) ? number_format($achi_prasant, 2) . "%" : "0.00 %",
                // 'tot_target_val' => $totTargetValue?$totTargetValue:"0.00",
                'tot_ach_val' => $user_ytd_currentMonthAchi ? number_format($user_ytd_currentMonthAchi, 2) : "0.00",
                'tot_ach_val_new' => $user_ytd_currentMonthAchi ? $user_ytd_currentMonthAchi : "0.00",
                'ach_%_ytd' => isset($ytd_value_av) ? number_format($ytd_value_av, 2) : "0.00%",
            ];
        }

        $results = collect($formatedresulst);
        $newRow = [];
        $newRow = [
            'special' => true,
            'name' => NULL,
            'orig_target_val' => number_format($results->sum('orig_target_val_new'),2),
            'month_ach_val' => number_format($results->sum('month_ach_val_new'),2),
            'ach_%' => NULL,
            'tot_ach_val' => number_format($results->sum('tot_ach_val_new'),2),
            'ach_%_ytd' => NULL

        ];

        $formatedresulst[] = $newRow;


        return [
            'count' => $count,
            'results' => $formatedresulst
        ];
    }

    protected function makeQuery($towns, $fromDate, $toDate, $teamId, $userId)
    {

        $invoices = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'), $userId)
            ->join('product AS p', 'il.product_id', '=', 'p.product_id')
            ->join('chemist AS c', 'c.chemist_id', 'il.chemist_id')
            ->join('sub_town AS st', 'st.sub_twn_id', 'c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'il.identity',
                'il.product_id',
                'p.product_code',
                'p.product_name',
                InvoiceLine::salesAmountColumn('bdgt_value'),
                InvoiceLine::salesQtyColumn('gross_qty'),
                InvoiceLine::salesQtyColumn('net_qty'),
                //  DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS gross_qty'),
                DB::raw('0 AS return_qty'),
                //  DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                //  DB::raw('ROUND(IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * Ifnull(Sum(il.invoiced_qty), 0),2) AS bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
            ]), 'c.sub_twn_id', $towns->pluck('sub_twn_id')->all())
            ->whereDate('il.invoice_date', '<=', $toDate)
            ->whereDate('il.invoice_date', '>=', $fromDate)
            ->groupBy('il.product_id')
            ->get();


        $returns = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'), $userId, true)
            ->join('product AS p', 'rl.product_id', '=', 'p.product_id')
            // ->join('sub_town AS st','st.sub_twn_code','rl.city')
            // ->join('sub_town AS st','st.sub_twn_id','rl.sub_twn_id')
            ->join('chemist AS c', 'c.chemist_id', 'rl.chemist_id')
            ->join('sub_town AS st', 'st.sub_twn_id', 'c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->select([
                'rl.identity',
                'rl.product_id',
                'p.product_code',
                'p.product_name',
                DB::raw('0 AS gross_qty'),
                InvoiceLine::salesAmountColumn('rt_bdgt_value', true),
                InvoiceLine::salesQtyColumn('return_qty', true),
                DB::raw('IFNULL(SUM(rl.invoiced_qty),0) AS return_qty'),
                DB::raw('0 AS net_qty'),
                DB::raw('0 AS bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
            ]), 'c.sub_twn_id', $towns->pluck('sub_twn_id')->all(), true)
            ->whereDate('rl.invoice_date', '<=', $toDate)
            ->whereDate('rl.invoice_date', '>=', $fromDate)
            ->groupBy('rl.product_id')
            ->get();

        $allProducts = $invoices->merge($returns);
        $allProducts->all();

        $allProducts = $allProducts->unique(function ($item) {
            return $item->product_code;
        });
        $allProducts->values()->all();

        $results = $allProducts->values();
        $results->all();

        $results->transform(function ($row) use ($results, $returns) {
            $grossQty = 0;
            $netQty = 0;
            $rtnQty = 0;
            $netValue = 0;
            foreach ($results as $inv) {
                if ($row->product_id == $inv->product_id) {
                    $netValue += $inv->bdgt_value;
                    $netQty += $inv->net_qty;
                }
            }
            foreach ($returns as $rtn) {
                if ($row->product_id == $rtn->product_id) {
                    $netValue -= $rtn->rt_bdgt_value;
                    $netQty -= $rtn->return_qty;
                }
            }


            return [
                'product_id' => $row->product_id,
                'qty' => $netQty,
                'amount' => round($netValue, 2)
            ];
        });



        return $results;
    }

    protected function getTargetsForMonth($userId, $pro_id, $brand_id, $prin_id, $year, $month)
    {

        $target = UserTarget::where('u_id', $userId)
            ->where('ut_month', $month)
            ->where('ut_year', $year)
            ->latest()
            ->first();

        if (!$target)
            return json_decode('{"upt_value":0,"upt_qty":0}');

        $user_product_target = UserProductTarget::where('ut_id', $target['ut_id'])
            ->where('product_id', $pro_id)
            ->select('upt_value', 'upt_qty')
            ->first();

        return $user_product_target ?? json_decode('{ "upt_value":0,"upt_qty":0 }');
    }

    protected function setColumns(ColumnController $columnController, Request $request)
    {

        $columnController->text('emp_no')->setLabel("Emp No");
        $columnController->text('name')->setLabel("Name");

        $columnController->number('orig_target_val')->setLabel("Original Target Value");
        $columnController->number('month_ach_val')->setLabel("Month Ach Value");
        $columnController->number('ach_%')->setLabel("Ach%");

        // $columnController->text('tot_target_val')->setLabel("Total Target Value");
        $columnController->number('tot_ach_val')->setLabel("Total Ach Value");
        $columnController->number('ach_%_ytd')->setLabel("Ach%");
    }
    protected function getAdditionalHeaders($request)
    {
        $columns = [[
            [
                "title" => "",
                "colSpan" => 2
            ],
            [
                "title" => "Month",
                "colSpan" => 3
            ],
            [
                "title" => "YTD Total",
                "colSpan" => 2
            ]
        ]];
        return $columns;
    }
    protected function setInputs($inputController)
    {
        $inputController->ajax_dropdown("area_name")->setLabel("Area")->setLink("Area")->setValidations('');
        $inputController->ajax_dropdown("divi_name")->setLabel("Division")->setLink("Division")->setValidations('');
        $inputController->date("month")->setLabel("Financial Month");
        $inputController->text("year")->setLabel("Year");
        $inputController->ajax_dropdown("pri_name")->setLabel("Principal")->setLink("principal")->setValidations('');
        $inputController->ajax_dropdown("team_name")->setLabel("Team")->setLink('team')->setWhere([
            'divi_id' => '{divi_name}'
        ]);
        $inputController->ajax_dropdown("user_id")->setLabel("PS/MR Name")->setWhere(['u_tp_id' => '3'.'|'.config('shl.product_specialist_type')])->setWhere(['tm_id' => "{team_name}"])->setLink("user")->setValidations('');
        $inputController->setStructure([
            ["team_name", "user_id", "month"],
        ]);
    }
}
