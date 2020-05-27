<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExecutiveSaleReportController extends ReportController
{
    protected $title = "Executive Wise Sales Report";

    public function search(Request $request)
    {
        $values = $request->input('values');

        $dateFrom = date('Y-m', strtotime($values['month'])) . '-' . '01';
        $dateTo = date('Y-m', strtotime($values['month'])) . '-' . date('d');

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
            ->where('p.divi_id', 2)
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
            ->where('p.divi_id', 2)
            ->whereNull('il.deleted_at')
            ->whereNull('pi.deleted_at')
            ->whereNull('p.deleted_at')
            ->groupBy('il.salesman_code');

        if (isset($values['user'])) {
            $user = User::where('id',$values['user']['value'])->first();
            // $invoice->where('il.u_id', $values['user']['value']);
            // $return->where('il.u_id', $values['user']['value']);
            $invoice->where('il.salesman_code',$user->u_code);
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

        $allachivements = $invoices->concat($returns);

        $users = User::whereIn('u_code', $allachivements->pluck('salesman_code')->all());
        $count = $this->paginateAndCount($users, $request, 'id');

        $results = $users->get();

        $dateFrom = date('Y-m', strtotime($values['month'])) . '-' . '01';
        $dateTo = date('Y-m', strtotime($values['month'])) . '-' . date('d');

        $results->transform(function ($row) use ($invoices, $returns, $dateFrom, $dateTo) {
            // $salesAchi = $invoices->where('u_id', $row->id)->sum('bdgt_value');
            // $returnAchi = $returns->where('u_id', $row->id)->sum('bdgt_value');
            $salesAchi = $invoices->where('salesman_code', $row->u_code)->sum('bdgt_value');
            $returnAchi = $returns->where('salesman_code', $row->u_code)->sum('bdgt_value');

            $exeSales = $this->getCurrentDaySale(2, $row->u_code, date('Y-m-d'), date('Y-m-d'));
            $exeSalesCu = $this->getCurrentDaySale(null, $row->u_code, date('Y-m-d'), date('Y-m-d'));
            $exeSalesMonthCu = $this->getCurrentDaySale(null, $row->u_code, $dateFrom, $dateTo);

            if (isset($salesAchi) && isset($returnAchi)) {
                $exeSale = $salesAchi - $returnAchi;
            }

            $return['exe_code'] = isset($row->u_code) ? $row->u_code : '-';
            $return['exe_name'] = isset($row->name) ? $row->name : '-';
            $return['curr_day_sales'] = isset($exeSales) ? number_format($exeSales, 2) : 0;
            $return['curr_day_sales_new'] = isset($exeSales) ? $exeSales : 0;
            $return['cum_sales'] = isset($exeSale) ? number_format($exeSale, 2) : 0;
            $return['cum_sales_new'] = isset($exeSale) ? $exeSale : 0;
            $return['tot_curr_day_sales'] = isset($exeSalesCu) ? number_format($exeSalesCu, 2) : 0;
            $return['tot_curr_day_sales_new'] = isset($exeSalesCu) ? $exeSalesCu : 0;
            $return['tot_cum_sales'] = isset($exeSalesMonthCu) ? number_format($exeSalesMonthCu, 2) : 0;
            $return['tot_cum_sales_new'] = isset($exeSalesMonthCu) ? $exeSalesMonthCu : 0;
            return $return;
        });

        $row = [
            'special' => true,
            'exe_code' => 'Total',
            'exe_name' => null,
            'curr_day_sales' => number_format($results->sum('curr_day_sales_new'), 2),
            'cum_sales' => number_format($results->sum('cum_sales_new'), 2),
            'tot_curr_day_sales' => number_format($results->sum('tot_curr_day_sales_new'), 2),
            'tot_cum_sales' => number_format($results->sum('tot_cum_sales_new'), 2),
        ];
        $results->push($row);

        return [
            'results' => $results,
            'count' => $count,
        ];
    }

    protected function getCurrentDaySale($divi_id, $user, $form, $to)
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
            ->whereNull('p.deleted_at')
            ->groupBy('il.salesman_code');

        if (isset($divi_id)) {
            $invoice->where('p.divi_id', 2);
            $return->where('p.divi_id', 2);
        }

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

        $columns = [[
            [
                "title" => "",
                "colSpan" => 2,
            ],
            [
                "title" => "Pharma Sales",
                "colSpan" => 2,
            ],
            [
                "title" => "Total Sales",
                "colSpan" => 2,
            ],
        ]];

        return $columns;
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('exe_code')->setLabel('Executive Code');
        $columnController->text('exe_name')->setLabel('Executive Name');

        $columnController->text('curr_day_sales')->setLabel('Current Day Sales');
        $columnController->text('cum_sales')->setLabel('Cumulative Sales');

        $columnController->text('tot_curr_day_sales')->setLabel('Current Day Sales');
        $columnController->text('tot_cum_sales')->setLabel('Cumulative Sales');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('chemist')->setLabel('Chemist')->setLink('chemist');
        $inputController->ajax_dropdown('user')->setLabel('User')->setWhere(['u_tp_id' => 10])->setLink('user');
        $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product')->setValidations('');
        $inputController->date('month')->setLabel('Month')->setLink('month');
        $inputController->date('e_date')->setLabel('To')->setLink('e_date');

        $inputController->setStructure([
            ['user', 'month'],
        ]);
    }
}
