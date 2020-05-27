<?php

namespace App\Http\Controllers\WebView\Sales;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use \Illuminate\Support\Facades\Auth;

class ExecutiveSaleController extends Controller
{

    public function index()
    {

        return view('WebView/Sales.exe_wise_sales');
    }

    public function getExeWiseSales(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'date_month' => 'required',
        ]);

        $user = Auth::user();

        $time = time();

        if (!$validation->fails()) {
            $time = strtotime($request->input('date_month') . "-01");
        }

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

        if (isset($user)) {

            $user = User::where('id', $user->getKey())->first();
            $invoice->where('il.salesman_code', $user->u_code);
            $return->where('il.salesman_code', $user->u_code);

            // $invoice->where('il.u_id', $user->getKey());
            // $return->where('il.u_id', $user->getKey());
        }

        if (isset($time)) {
            $invoice->whereDate('il.invoice_date', '>=', date('Y-m-01', $time));
            $invoice->whereDate('il.invoice_date', '<=', date('Y-m-d'));

            $return->whereDate('il.invoice_date', '>=', date('Y-m-01', $time));
            $return->whereDate('il.invoice_date', '<=', date('Y-m-d'));
        }

        $invoices = $invoice->get();
        $returns = $return->get();

        $allachivements = $invoices->concat($returns);

        $users = User::whereIn('u_code', $allachivements->pluck('salesman_code')->all());

        $results = $users->get();

        $dateFrom = date('Y-m', $time) . '-' . '01';
        $dateTo = date('Y-m', $time) . '-' . date('d');

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
            //   $return['curr_day_sales_new'] = isset($exeSales) ? $exeSales : 0;
            $return['cum_sales'] = isset($exeSale) ? number_format($exeSale, 2) : 0;
            //   $return['cum_sales_new'] = isset($exeSalesMonth) ? $exeSalesMonth : 0;
            $return['tot_curr_day_sales'] = isset($exeSalesCu)?number_format($exeSalesCu,2):0;
            //   $return['tot_curr_day_sales_new'] = isset($exeSales) ? $exeSales : 0;
            $return['tot_cum_sales'] = isset($exeSalesMonthCu)?number_format($exeSalesMonthCu,2):0;
            //   $return['tot_cum_sales_new'] = isset($exeSale) ? $exeSale : 0;
            return $return;
        });

        return view('WebView/Sales.exe_wise_sales_search', ['exeWiseSales' => $results]);
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
}
