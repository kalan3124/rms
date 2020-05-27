<?php
namespace App\Http\Controllers\WebView\Sales;

use App\Http\Controllers\Controller;
use App\Models\Chemist;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaCustomerTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use \Illuminate\Support\Facades\Auth;

class CustomerWiseSaleController extends Controller
{

    public function index()
    {
        return view('WebView/Sales.cus_wise_sale');
    }

    public function getCusWiseSales(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'date_month' => 'required',
        ]);

        $user = Auth::user();

        $time = time();

        if (!$validation->fails()) {
            $time = strtotime($request->input('date_month') . "-01");
        }

        $invoice = DB::table('chemist as c')
            ->join('invoice_line as il', 'il.chemist_id', 'c.chemist_id')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
            })
            ->select([
                'c.chemist_id',
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.chemist_id');

        $return = DB::table('chemist as c')
            ->join('return_lines as il', 'il.chemist_id', 'c.chemist_id')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
            })
            ->select([
                'c.chemist_id',
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.chemist_id');

        if (isset($user)) {
            $products = SalesmanValidPart::where('u_id', $user->getKey())->whereDate('from_date', '<=', date('Y-m-01', $time))->whereDate('to_date', '>=', date('Y-m-t', $time))->get();
            $chemists = SalesmanValidCustomer::where('u_id', $user->getKey())->whereDate('from_date', '<=', date('Y-m-01', $time))->whereDate('to_date', '>=', date('Y-m-t', $time))->get();

            $invoice->whereIn('il.product_id', $products->pluck('product_id')->all());
            $invoice->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

            $return->whereIn('il.product_id', $products->pluck('product_id')->all());
            $return->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());
        }

        if (isset($time)) {
            $invoice->whereDate('il.invoice_date', '>=', date('Y-m-01', $time));
            $invoice->whereDate('il.invoice_date', '<=', date('Y-m-t', $time));

            $return->whereDate('il.invoice_date', '>=', date('Y-m-01', $time));
            $return->whereDate('il.invoice_date', '<=', date('Y-m-t', $time));
        }

        $invoices = $invoice->get();
        $returns = $return->get();

        $allachivements = $invoices->concat($returns);

        $chemist = Chemist::whereIn('chemist_id', $allachivements->pluck('chemist_id')->all());

        $results = $chemist->get();

        $chemistAchi = 0;
        $balance = 0;
        $achi = 0;
        $results->transform(function ($row) use ($invoices, $returns, $time, $chemistAchi, $balance, $achi) {

            $salesAchi = $invoices->where('chemist_id', $row->chemist_id)->sum('bdgt_value');
            $returnAchi = $returns->where('chemist_id', $row->chemist_id)->sum('bdgt_value');

            if (isset($salesAchi) && isset($returnAchi)) {
                $chemistAchi = $salesAchi - $returnAchi;
            }

            $targets = SfaCustomerTarget::where('sfa_cus_code', $row->chemist_id)->where('sfa_year', date('Y', $time))->where('sfa_month', date('m', $time))->first();

            if ((isset($targets->sfa_target) && isset($chemistAchi) && ($targets->sfa_target > 0 && $chemistAchi > 0))) {
                $balance = $targets->sfa_target - $chemistAchi;
            }

            if ((isset($targets->sfa_target) && isset($chemistAchi) && ($targets->sfa_target > 0 && $chemistAchi > 0))) {
                $achi = $chemistAchi / $targets->sfa_target * 100;
            }

            $return['chemist'] = isset($row->chemist_code) ? $row->chemist_code : '-';
            $return['chemist_name'] = isset($row->chemist_name) ? $row->chemist_name : '-';
            $return['target'] = isset($targets->sfa_target) ? number_format($targets->sfa_target, 2) : 0;
          //   $return['target_new'] = isset($targets->sfa_target) ? $targets->sfa_target : 0;
            $return['achi'] = isset($chemistAchi) ? number_format($chemistAchi, 2) : 0;
          //   $return['achi_new'] = isset($chemistAchi) ? $chemistAchi : 0;
            $return['ach_%'] = isset($achi) ? round($achi, 2) : 0;
            $return['balance'] = isset($balance) ? number_format($balance, 2) : 0;
          //   $return['balance_new'] = isset($balance) ? $balance : 0;
            return $return;
        });

        return view('WebView/Sales.cus_wise_sale_search', ['cusWiseSales' => $results]);
    }
}
