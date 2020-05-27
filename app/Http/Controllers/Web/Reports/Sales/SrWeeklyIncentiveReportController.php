<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Area;
use App\Models\Product;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Foreach_;

class SrWeeklyIncentiveReportController extends ReportController
{

    protected $title = "SR Weekly Incentive Report";

    public function search($request)
    {
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        $dateFrom = date('Y-m-01', strtotime($values['month']));
        $dateTo = date('Y-m-t', strtotime($values['month']));

        $i = 1;
        $week = [];
        for ($date = $dateFrom; $date <= $dateTo; $date = date('Y-m-d', strtotime($date . ' + 7 days'))) {
            $week[$i . '_week'] = $this->getWeekDates($date, $dateFrom, $dateTo, $i);
            $i++;
        }

        // return substr($week['2_week'],0,10);
        // return substr($week['5_week'],13,23);

        // return $week;die;

        $query = User::query();
        $query->where('u_tp_id', 10);
        $query->where('id', '!=', 420);
        $query->where('id', '!=', 379);
        $query->where('id', '!=', 419);

        if (isset($values['user'])) {
            $query->where('id', $values['user']['value']);
        }

        if (isset($values['area'])) {
            $area = Area::where('ar_id', $values['area']['value'])->first();
            $users = User::where('u_code', 'LIKE', '%' . $area->ar_code . '%')->get();
            $query->whereIn('id', $users->pluck('id')->all());
        }

        $count = $this->paginateAndCount($query, $request, 'u_code');
        $results = $query->get();

        $newResults = [];
        $results->transform(function ($val, $key) use ($values, $week, $dateFrom, $dateTo) {
            $userCode = substr($val->u_code, 0, 4);
            $return['u_code'] = $userCode;
            $area = Area::where('ar_code', $userCode)->first();

            $query = SfaTarget::query();
            $query->where('u_id', $val->id);
            $query->where('trg_year', date('Y', strtotime($values['month'])));
            $query->where('trg_month', date('m', strtotime($values['month'])));
            $query->latest();
            $target_user = $query->first();

            $target_user = SfaTargetProduct::where('sfa_trg_id', $target_user['sfa_trg_id'])->sum('stp_amount');

            $achiMonth = $this->makeQuery($val->id, $dateFrom, $dateTo);

            $achi1 = $this->makeQuery($val->id, substr($week['1_week'], 0, 10), substr($week['1_week'], 13, 23));
            $achi2 = $this->makeQuery($val->id, substr($week['2_week'], 0, 10), substr($week['2_week'], 13, 23));
            $achi3 = $this->makeQuery($val->id, substr($week['3_week'], 0, 10), substr($week['3_week'], 13, 23));
            $achi4 = $this->makeQuery($val->id, substr($week['4_week'], 0, 10), substr($week['4_week'], 13, 23));
            $achi5 = $this->makeQuery($val->id, substr($week['5_week'], 0, 10), substr($week['5_week'], 13, 23));

            $achi1_pre = 0;
            $achi2_pre = 0;
            $achi3_pre = 0;
            $achi4_pre = 0;

            if (isset($target_user) && $target_user > 0) {
                if (isset($achi1) && $achi1 > 0) {
                    $achi1_pre = $achi1 / $target_user * 100;
                }

                if (isset($achi2) && $achi2 > 0) {
                    $achi2_pre = $achi2 / $target_user * 100;
                }

                if (isset($achi3) && $achi3 > 0) {
                    $achi3_pre = $achi3 / $target_user * 100;
                }

                if (isset($achi4) && $achi4 > 0) {
                    $achi4_pre = $achi4 / $target_user * 100;
                }

                if (isset($achiMonth) && $achiMonth > 0) {
                    $achiMonthPer = $achiMonth / $target_user * 100;
                }
            }

            $return['ar_name'] = isset($area->ar_name) ? $area->ar_name : '';
            $return['sr_name'] = $val->name;
            $return['sr_code'] = $val->u_code ? $val->u_code : '';
            $return['ori_target'] = isset($target_user) ? number_format($target_user, 2) : 0;
            $return['ori_target_new'] = isset($target_user) ? $target_user : 0;
            $return['sr_range'] = NULL;
            $return['act_val'] = isset($achiMonth) ? number_format($achiMonth, 2) : 0;
            $return['act_val_new'] = isset($achiMonth) ? $achiMonth : 0;
            $return['month'] = isset($achiMonthPer) ? round($achiMonthPer, 2) : 0;
            $return['1st_week'] = isset($achi1) ? number_format($achi1) : 0;
            $return['25_%'] = $achi1_pre != 0  ? round($achi1_pre, 2) : 0;
            $return['2nd_week'] = isset($achi2) ? number_format($achi2) : 0;
            $return['40_%'] = $achi2_pre != 0 ? round($achi2_pre, 2) : 0;
            $return['3rd_week'] = isset($achi3) ? number_format($achi3) : 0;
            $return['65_%'] = $achi3_pre != 0 ? round($achi3_pre, 2) : 0;
            $return['4th_week'] = isset($achi4) ? number_format($achi4) : 0;
            $return['100_%'] = $achi4_pre != 0 ? round($achi4_pre, 2) : 0;
            $return['5th_week'] = isset($achi5) ? number_format($achi5) : 0;


            return $return;
        });

        $tot_target_amount = 0;
        $tot_achi_amount = 0;
        foreach ($results as $key => $value) {
            $tot_target_amount += $value['ori_target_new'];
            $tot_achi_amount += $value['act_val_new'];
            $newResults[] = $value;
            if ($key != count($results) - 1) {
                if (isset($value['u_code'])) {
                    $perCode = $results[$key + 1];
                    $area = Area::where('ar_code', $value['u_code'])->first();
                    if ($value['u_code'] != $perCode['u_code']) {
                        $newResults[] = [
                            'ar_name' => 'Total Of ' . $area->ar_name,
                            'ori_target' => number_format($tot_target_amount, 2),
                            'act_val' => number_format($tot_achi_amount, 2),
                            'special' => true,
                        ];
                        $tot_target_amount = 0;
                        $tot_achi_amount = 0;
                    }
                }
            }
        }

        $amount = $results->sum('ori_target_new');
        $amountAch = $results->sum('act_val_new');
        $amountAchNew = $results->sum('ach_new');

        $newResults[] = [
            'ar_name' => 'Total ',
            'ori_target' => number_format($amount, 2, '.', ''),
            'act_val' => number_format($amountAch, 2, '.', ''),
            'ach' => number_format($amountAchNew, 2, '.', ''),
            'special' => true
        ];

        return [
            'results' => $newResults,
            'count' => $count,
        ];
    }

    function makeQuery($user, $dateFrom, $dateTo)
    {
        $invoice = DB::table('invoice_line as il')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                // 'il.u_id',
                'il.salesman_code',
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            // ->where('p.divi_id', 2)
            ->whereNull('il.deleted_at')
            ->whereNull('pi.deleted_at')
            ->whereNull('p.deleted_at')
            ->groupBy('il.salesman_code');

        $return = DB::table('return_lines as il')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                // 'il.u_id',
                'il.salesman_code',
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            // ->where('p.divi_id', 2)
            ->whereNull('il.deleted_at')
            ->whereNull('pi.deleted_at')
            ->whereNull('p.deleted_at')
            ->groupBy('il.salesman_code');

        if (isset($user)) {
            $user = User::where('id', $user)->first();
            $invoice->where('il.salesman_code', $user->u_code);
            $return->where('il.salesman_code', $user->u_code);
        }

        if (isset($dateFrom) && isset($dateTo)) {
            $invoice->whereDate('il.invoice_date', '>=', $dateFrom);
            $invoice->whereDate('il.invoice_date', '<=', $dateTo);

            $return->whereDate('il.invoice_date', '>=', $dateFrom);
            $return->whereDate('il.invoice_date', '<=', $dateTo);
        }

        $invoices = $invoice->get();
        $returns = $return->get();

        $tot_inv = 0;
        $tot_ret = 0;
        foreach ($invoices as $key => $value) {
            $tot_inv += $value->bdgt_value;
        }

        foreach ($returns as $key => $value) {
            $tot_ret += $value->bdgt_value;
        }

        $total = $tot_inv - $tot_ret;

        return $total;
    }

    function getWeekDates($date, $start_date, $end_date, $i)
    {
        $week =  date('W', strtotime($date));
        $year =  date('Y', strtotime($date));

        $from = date("Y-m-d", strtotime("{$year}-W{$week}+1"));
        if ($from < $start_date) $from = $start_date;

        $to = date("Y-m-d", strtotime("{$year}-W{$week}-7"));
        if ($to > $end_date) $to = $end_date;

        return date('Y-m-d', strtotime($from)) . " - " . date('Y-m-d', strtotime($to));
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('ar_name')->setLabel('Area');
        $columnController->text('sr_name')->setLabel('SR Name');
        $columnController->number('sr_code')->setLabel('SR Code');
        $columnController->number('sr_range')->setLabel('SR Range');

        $columnController->number('ori_target')->setLabel('Original target');
        $columnController->number('act_val')->setLabel('Actuals');
        $columnController->number('month')->setLabel('Month %');
        $columnController->number('1st_week')->setLabel('1st Week');
        $columnController->number('25_%')->setLabel('25%');
        $columnController->number('2nd_week')->setLabel('2nd Week');
        $columnController->number('40_%')->setLabel('40%');
        $columnController->number('3rd_week')->setLabel('3rd Week');
        $columnController->number('65_%')->setLabel('65%');
        $columnController->number('4th_week')->setLabel('4th Week');
        $columnController->number('100_%')->setLabel('100%');
        $columnController->number('4th_week')->setLabel('4th Week');
        $columnController->number('5th_week')->setLabel('5th Week');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type')])->setValidations('');
        $inputController->ajax_dropdown('area')->setLabel('Area')->setLink('area')->setValidations('');
        $inputController->date('month')->setLabel('Month');

        $inputController->setStructure([
            ['area', 'user', 'month'],
        ]);
    }
}
