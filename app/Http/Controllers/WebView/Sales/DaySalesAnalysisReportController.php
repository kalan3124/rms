<?php

namespace App\Http\Controllers\WebView\Sales;

use App\Http\Controllers\Controller;
use App\Models\SfaDailyTarget;
use App\Models\SfaSalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use \Illuminate\Support\Facades\Auth;

class DaySalesAnalysisReportController extends Controller
{

    public function index()
    {

        return view('WebView/Sales.day_analysis');
    }

    public function getDaySales(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'date_month' => 'required',
        ]);

        $user = Auth::user();

        $time = time();

        if (!$validation->fails()) {
            $time = strtotime($request->input('date_month') . "-01");
        }

        $start = new \DateTime(date('Y-m-01', $time));
        $end = new \DateTime(date('Y-m-t', $time));
        $end = $end->modify('1 day');

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end);

        $formattedResults = [];

        $month_target = 0;
        $sale_order = 0;
        $achi_c = 0;
        $newResults = [];
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
                $achi_pre = $achi_c / $month_target * 100;
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
                'achi' => isset($achi) ? $achi : 0,
                'achi_new' => isset($achi) ? $achi : 0,
                'precentage' => isset($achi_pre) ? $achi_pre : 0,
                'month_target' => isset($month_target) ? number_format($month_target, 2) : 0,
                'cu_order_value' => isset($sale_order) ? number_format($sale_order, 2) : 0,
                'cu_achi' => isset($achi_c) ? number_format($achi_c, 2) : 0,
                'c_precentage' => isset($achi_pre) ? $achi_pre : 0,
                'defict' => isset($def) ? number_format($def, 2) : 0,
                'to_be' => isset($invoiced) ? number_format($invoiced, 2) : 0,
            ];
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
        ];

        array_push($newResults, $row);

        return view('WebView/Sales.day_analysis_search', ['daySales' => $newResults]);
    }

    protected function getDayAchi($user, $form, $to)
    {
        $invoice = DB::table('invoice_line as il')
            ->leftJoin('latest_price_informations AS pi', function ($query) {
                $query->on('pi.product_id', '=', 'p.product_id');
                $query->on('pi.year', '=', DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
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
            ->leftJoin('latest_price_informations AS pi', function ($query) {
                $query->on('pi.product_id', '=', 'p.product_id');
                $query->on('pi.year', '=', DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
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
}
