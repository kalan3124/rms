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

class SrTargetVsAchivementReportController extends ReportController
{

    protected $title = "SR Wise Orginal Target vs Achivement  Report";

    public function search($request)
    {
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'sr_name':
                $sortBy = 'name';
                break;
            case 'sr_code':
                $sortBy = 'u_code';
                break;
            default:
                $sortBy = 'u_code';
                break;
        }

        $dateFrom = date('Y-m-01', strtotime($values['month']));
        $dateTo = date('Y-m-t', strtotime($values['month']));

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

        if (isset($values['user'])) {
            $user = User::where('id', $values['user']['value'])->first();
            $invoice->where('il.salesman_code', $user->u_code);
            $return->where('il.salesman_code', $user->u_code);
        }

        if (isset($values['month'])) {
            $invoice->whereDate('il.invoice_date', '>=', $dateFrom);
            $invoice->whereDate('il.invoice_date', '<=', $dateTo);

            $return->whereDate('il.invoice_date', '>=', $dateFrom);
            $return->whereDate('il.invoice_date', '<=', $dateTo);
        }

        $invoices = $invoice->get();
        $returns = $return->get();

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

        $count = $this->paginateAndCount($query, $request, $sortBy);
        $results = $query->get();

        $newResults = [];
        $results->transform(function ($val, $key) use ($values, $invoices, $returns) {
            $userCode = substr($val->u_code, 0, 4);
            $return['u_code'] = $userCode;
            $area = Area::where('ar_code', $userCode)->first();

            $salesAchi = $invoices->where('salesman_code', $val->u_code)->sum('bdgt_value');
            $returnAchi = $returns->where('salesman_code', $val->u_code)->sum('bdgt_value');

            if (isset($salesAchi) && isset($returnAchi)) {
                $exeSale = $salesAchi - $returnAchi;
            }

            $query = SfaTarget::query();
            $query->where('u_id', $val->id);
            $query->where('trg_year', date('Y', strtotime($values['month'])));
            $query->where('trg_month', date('m', strtotime($values['month'])));
            $query->latest();
            $target_user = $query->first();

            $target_user = SfaTargetProduct::where('sfa_trg_id', $target_user['sfa_trg_id'])->sum('stp_amount');

            if (isset($exeSale) && isset($target_user) && ($exeSale > 0 && $target_user > 0)) {
                $achi = $exeSale / $target_user * 100;
            }

            $return['ar_name'] = isset($area->ar_name) ? $area->ar_name : '';
            $return['sr_name'] = $val->name;
            $return['sr_code'] = $val->u_code ? $val->u_code : '';
            $return['ori_target'] = isset($target_user) ? number_format($target_user, 2) : 0;
            $return['ori_target_new'] = isset($target_user) ? $target_user : 0;
            $return['sr_range'] = null;
            $return['act_val'] = isset($exeSale) ? number_format($exeSale, 2) : 0;
            $return['act_val_new'] = isset($exeSale) ? $exeSale : 0;
            $return['ach'] = isset($achi)?round($achi,2):0;
            $return['ach_new'] = isset($achi)?$achi:0;

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

        // return $newResults->sort('');

        return [
            'results' => $newResults,
            'count' => $count,
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('ar_name')->setLabel('Area');
        $columnController->text('sr_name')->setLabel('SR Name');
        $columnController->number('sr_code')->setLabel('SR Code');
        $columnController->number('sr_range')->setLabel('SR Range');

        $columnController->number('ori_target')->setLabel('Sum of Original target Value');
        $columnController->number('act_val')->setLabel('Sum of Actual Value');
        $columnController->number('ach')->setLabel('Ach %');
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
