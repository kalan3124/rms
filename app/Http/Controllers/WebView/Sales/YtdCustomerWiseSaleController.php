<?php
namespace App\Http\Controllers\WebView\Sales;

use App\Http\Controllers\Controller;
use App\Models\Chemist;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class YtdCustomerWiseSaleController extends Controller
{

    public function index()
    {
        return view('WebView/Sales.ytd_cus_wise_sale');
    }

    public function getYtdCusWiseSales(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'date_month' => 'required',
            'date_month2' => 'required',
        ]);

        $user = Auth::user();

        $time = time();

        if (!$validation->fails()) {
            $time = strtotime($request->input('date_month'));
            $time2 = strtotime($request->input('date_month2'));
        }

        $begin = new \DateTime(date('Y-m-01', $time));
        $end = new \DateTime(date('Y-m-d', $time2));

        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($begin, $interval, $end);

        $invoice = DB::table('chemist as c')
            ->join('invoice_line as il', 'il.chemist_id', 'c.chemist_id')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'c.chemist_id',
                DB::raw('YEAR(il.invoice_date) as year'),
                DB::raw('MONTH(il.invoice_date) as month'),
                DB::raw('DATE(il.invoice_date) as date'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.chemist_id', DB::raw('YEAR(il.invoice_date)'), DB::raw('MONTH(il.invoice_date)'));

        $return = DB::table('chemist as c')
            ->join('return_lines as il', 'il.chemist_id', 'c.chemist_id')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'c.chemist_id',
                DB::raw('YEAR(il.invoice_date) as year'),
                DB::raw('MONTH(il.invoice_date) as month'),
                DB::raw('DATE(il.invoice_date) as date'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.chemist_id', DB::raw('YEAR(il.invoice_date)'), DB::raw('MONTH(il.invoice_date)'));

        if (isset($user)) {
            $products = SalesmanValidPart::where('u_id', $user->getKey())->whereDate('from_date', '<=', date('Y-m-d', $time))->whereDate('to_date', '>=', date('Y-m-d', $time))->get();
            $chemists = SalesmanValidCustomer::where('u_id', $user->getKey())->whereDate('from_date', '<=', date('Y-m-d', $time2))->whereDate('to_date', '>=', date('Y-m-d', $time2))->get();

            $invoice->whereIn('il.product_id', $products->pluck('product_id')->all());
            $invoice->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

            $return->whereIn('il.product_id', $products->pluck('product_id')->all());
            $return->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());
        }

        if (isset($time) && isset($time2)) {
            $invoice->whereDate('il.invoice_date', '>=', date('Y-m-d', $time));
            $invoice->whereDate('il.invoice_date', '<=', date('Y-m-d', $time2));

            $return->whereDate('il.invoice_date', '>=', date('Y-m-d', $time));
            $return->whereDate('il.invoice_date', '<=', date('Y-m-d', $time2));
        }

        $invoices = $invoice->get();
        $returns = $return->get();

        $allachivements = $invoices->concat($returns);

        $chemist = Chemist::with('sub_town', 'chemist_class')->whereIn('chemist_id', $allachivements->pluck('chemist_id')->all());

        $results = $chemist->get();

        $chemistAchi = 0;
        $balance = 0;
        $achi = 0;
        $results->transform(function ($row) use ($invoices, $returns, $time,$time2, $chemistAchi, $balance, $achi, $period) {

            foreach ($period as $key => $month) {
                $salesAchi = $invoices->where('year', $month->format('Y'))->where('month', $month->format('m'))->where('chemist_id', $row->chemist_id)->sum('bdgt_value');
                $returnAchi = $returns->where('year', $month->format('Y'))->where('month', $month->format('m'))->where('chemist_id', $row->chemist_id)->sum('bdgt_value');

                if (isset($salesAchi) && isset($returnAchi)) {
                    $chemistAchi = $salesAchi - $returnAchi;
                }

                $return['month_' . $month->format('m')] = isset($chemistAchi) ? number_format($chemistAchi, 2) : 0;
                $return['month_new' . $month->format('m')] = isset($chemistAchi) ? $chemistAchi : 0;
            }

            $return['chemist'] = isset($row->chemist_code) ? $row->chemist_code : '-';
            $return['chemist_name'] = isset($row->chemist_name) ? $row->chemist_name : '-';
            $return['sub_name'] = isset($row->sub_town) ? $row->sub_town->sub_twn_name : '-';
            $return['class'] = isset($row->chemist_class) ? $row->chemist_class->chemist_class_name : '-';
            return $return;
        });

        return view('WebView/Sales.ytd_cus_wise_sale_search', ['ytdCusWiseSales' => $results, 'period' => $period]);
    }
}
