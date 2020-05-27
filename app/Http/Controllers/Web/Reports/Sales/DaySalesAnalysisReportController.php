<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\SfaDailyTarget;
use App\Models\SfaSalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DaySalesAnalysisReportController extends ReportController
{

    protected $title = "Daily Sales Analysis Report";

    public function search(Request $request)
    {

        $values = $request->input('values');

        $start = new \DateTime(date('Y-m-01', strtotime($values['month'])));
        $end = new \DateTime(date('Y-m-t', strtotime($values['month'])));
        $end = $end->modify('1 day');

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end);

        $formattedResults = [];
        $user = User::where('id', $values['rep'])->first();

        $month_target = 0;
        $sale_order = 0;
        $achi_c = 0;
        $newResults = [];
        $tot_day_target = 0;
        $tot_sales_order_value = 0;
        $tot_achi = 0;
        $tot_precentage = 0;
        $tot_month_target = 0;
        $tot_cu_order_value = 0;
        $tot_cu_achi = 0;
        $tot_c_precentage = 0;
        $tot_defict = 0;
        $tot_to_be = 0;
        foreach ($period as $key => $date) {
            $day_target = SfaDailyTarget::where('sr_code', $user->id)->where('target_day', $date->format('Y-m-d'))->first();
            $order_val = SfaSalesOrder::with('salesOrderProducts')->whereDate('order_date', '=', $date->format('Y-m-d'))->where('u_id', $user->id)->get();

            $achi = $this->getDayAchi($user->u_code, $date->format('Y-m-d'), $date->format('Y-m-d'));

            $total = 0;
            $order_val->transform(function ($val) use ($total) {
                $amont = $val->salesOrderProducts->transform(function ($row) use ($total) {
                    $total += $row->sales_qty * $row->price;
                    return $total;
                });
                return $amont->sum();
            });

            if (isset($day_target->day_target)) {
                $month_target += $day_target->day_target;
            }

            if (isset($order_val)) {
                $sale_order += isset($order_val) ? $order_val->sum() : 0;
            } else if ($order_val == "") {
                $sale_order = 0;
            }

            if ($achi != 0) {
                $achi_c += $achi;
            }

            if (isset($day_target->day_target) && isset($achi)) {
                $achi_pre = $achi / $day_target->day_target * 100;
            }

            if (isset($achi_c) && isset($month_target) && $achi_c > 0 && $month_target > 0) {
                $achi_pres = $achi_c / $month_target * 100;
                $def = $month_target - $achi_c;
            }

            if (isset($sale_order) && isset($achi_c)) {
                $invoiced = $sale_order - $achi_c;
            }

            $formattedResults[] = [
                'date' => $date->format('M-d'),
                'date_name' => $date->format('D'),
                'day_target' => isset($day_target->day_target) ? number_format($day_target->day_target, 2) : 0,
                'day_target_new' => isset($day_target->day_target) ? $day_target->day_target : 0,
                'sales_order_value' => isset($order_val) ? number_format($order_val->sum(), 2) : 0,
                'sales_order_value_new' => isset($order_val) ? $order_val->sum() : 0,
                'achi' => isset($achi) ? round($achi, 2) : 0,
                'achi_new' => isset($achi) ? $achi : 0,
                'precentage' => isset($achi_pre) ? round($achi_pre, 2) : 0,
                'month_target' => isset($month_target) ? number_format($month_target, 2) : 0,
                'cu_order_value' => isset($sale_order) ? number_format($sale_order, 2) : 0,
                'cu_achi' => isset($achi_c) ? number_format($achi_c, 2) : 0,
                'c_precentage' => isset($achi_pres) ? round($achi_pre, 2) : 0,
                'defict' => isset($def) ? number_format($def, 2) : 0,
                'to_be' => isset($invoiced) ? number_format($invoiced, 2) : 0,
            ];

            $tot_day_target += isset($day_target->day_target) ? $day_target->day_target : 0;
            $tot_sales_order_value = isset($order_val) ? $order_val->sum() : 0;
            $tot_achi = isset($achi) ? $achi : 0;
            $tot_precentage = isset($achi_pre) ? $achi_pre : 0;
            $tot_month_target = isset($month_target) ? $month_target : 0;
            $tot_cu_order_value = isset($sale_order) ? $sale_order : 0;
            $tot_cu_order_value = isset($achi_c) ? $achi_c : 0;
            $tot_cu_achi = isset($achi_c) ? $achi_c : 0;
            $tot_c_precentage = isset($achi_pre) ? $achi_pre : 0;
            $tot_defict = isset($def) ? $def : 0;
            $tot_to_be = isset($invoiced) ? $invoiced : 0;
        }
        $week = 0;
        $dayTarget = 0;
        $saleOrder = 0;
        $achi = 0;
        foreach ($formattedResults as $key => $value) {
            $newResults[] = $value;
            $saleOrder += $value['sales_order_value_new'];
            $dayTarget += $value['day_target_new'];
            $achi += $value['achi_new'];
            if ($value['date_name'] == "Sun") {
                $count = $week++;
                $newResults[] = [
                    'special' => true,
                    'date' => $count != 0 ? 'Week ' . $count : null,
                    'day_target' => $dayTarget ? number_format($dayTarget, 2) : 0,
                    'sales_order_value' => $saleOrder ? number_format($saleOrder, 2) : 0,
                    'achi' => $achi ? number_format($achi, 2) : 0,
                ];
                $dayTarget = 0;
                $saleOrder = 0;
                $achi = 0;
            }
        }

        $row = [
            'special' => true,
            'date' => "",
            'date_name' => "",
            'day_target' => number_format($tot_day_target, 2),
            'sales_order_value' => number_format($tot_sales_order_value, 2),
            'achi' => round($tot_achi, 2),
            'precentage' => round($tot_precentage, 2),
            'month_target' => number_format($tot_month_target, 2),
            'cu_order_value' => number_format($tot_cu_order_value, 2),
            'cu_achi' => number_format($tot_cu_achi, 2),
            'c_precentage' => round($tot_c_precentage, 2),
            'defict' => number_format($tot_defict, 2),
            'to_be' => number_format($tot_to_be, 2),

        ];

        array_push($newResults, $row);

        return [
            'results' => $newResults,
            'count' => 0,
        ];
    }

    protected function getDayAchi($user, $form, $to)
    {
        $invoice = DB::table('invoice_line as il')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                // 'il.u_id',
                'il.salesman_code',
                DB::raw('DATE(il.invoice_date) as date'),
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            // ->where('il.u_id', $user)
            ->where('il.salesman_code', $user)
            ->whereDate('il.invoice_date', '>=', $form)
            ->whereDate('il.invoice_date', '<=', $to)
            ->whereNull('il.deleted_at')
            ->whereNull('pi.deleted_at')
            // ->groupBy('il.u_id');
            ->groupBy('il.salesman_code');

        $return = DB::table('return_lines as il')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                // 'il.u_id',
                'il.salesman_code',
                DB::raw('DATE(il.invoice_date) as date'),
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            // ->where('il.u_id', $user)
            ->where('il.salesman_code', $user)
            ->whereDate('il.invoice_date', '>=', $form)
            ->whereDate('il.invoice_date', '<=', $to)
            ->whereNull('il.deleted_at')
            ->whereNull('pi.deleted_at')
            // ->groupBy('il.u_id');
            ->groupBy('il.salesman_code');

        $invoices = $invoice->get();
        $returns = $return->get();

        $inv = 0;
        $rev = 0;

        foreach ($invoices as $key => $val) {
            $inv += $val->bdgt_value;
        }

        foreach ($returns as $key => $val) {
            $rev += $val->bdgt_value;
        }

        $sales = $inv - $rev;

        return $sales;
    }

    protected function getAdditionalHeaders($request)
    {

        $row1 = [
            "title" => "",
            "colSpan" => 1,
        ];

        $row2 = [
            "title" => "",
            "colSpan" => 1,
        ];

        $row3 = [
            "title" => "",
            "colSpan" => 4,
        ];

        $row4 = [
            "title" => "CUMULATIVE",
            "colSpan" => 4,
        ];

        $row5 = [
            "title" => "",
            "colSpan" => 2,
        ];

        $columns = [[
            $row1,
            $row2,
            $row3,
            $row4,
            $row5,
        ]];
        return $columns;
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text("date")->setLabel("DATE");
        $columnController->text("date_name")->setLabel("");
        $columnController->text("day_target")->setLabel("Day Target");
        $columnController->text("sales_order_value")->setLabel("Sales Order Value");
        $columnController->text("achi")->setLabel("Achievement");
        $columnController->text("precentage")->setLabel("%");
        $columnController->text("month_target")->setLabel("Month Target");
        $columnController->text("cu_order_value")->setLabel("Sales Order Value");
        $columnController->text("cu_achi")->setLabel("Achievement");
        $columnController->text("c_precentage")->setLabel("%");
        $columnController->text("defict")->setLabel("DEFFICT");
        $columnController->text("to_be")->setLabel("TO BE INVOICED");
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown("rep")->setWhere(['u_tp_id' => '10'])->setLabel("SR")->setLink("user");
        $inputController->ajax_dropdown("area")->setLabel("Area")->setLink("area");
        $inputController->date("month")->setLabel("Month");

        $inputController->setStructure([["rep", "area", "month"]]);
    }
}
