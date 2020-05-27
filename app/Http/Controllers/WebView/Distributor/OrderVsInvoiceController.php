<?php
namespace App\Http\Controllers\WebView\Distributor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use \Illuminate\Support\Facades\Auth;

class OrderVsInvoiceController extends Controller
{
    public function index()
    {
        return view('WebView/Distributor.dis_order_vs_invoice');
    }
    public function getOrderVsInvoice(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'date_month' => 'required',
            'date_month2' => 'required',
        ]);

        $time = time();

        if (!$validation->fails()) {
            $time = strtotime($request->input('date_month'));
            $time2 = strtotime($request->input('date_month2'));
        }

        $user = Auth::user();

        $query = DB::table('distributor_sales_order as dso')
            ->select([
                'dsop.product_id',
                'p.product_code',
                'p.product_name',
                'dsr.u_code AS dsr_code',
                'dsr.name AS dsr_name',
                'dis.name AS dis_name',
                'dsr.id AS dsr_id',
                'dis.id AS dis_id',
                'dso.order_date',
                'pr.principal_name',
                'dsop.sales_qty',
                'dsop.price',
                'dso.dist_order_id',
                'dil.dil_qty',
                'dil.dil_unit_price',
                'dso.order_no'
            ])
            ->join('distributor_sales_order_products as dsop', 'dsop.dist_order_id', 'dso.dist_order_id')
            ->join('users AS dsr', 'dsr.id', '=', 'dso.u_id')
            ->join('users AS dis', 'dis.id', '=', 'dso.dis_id')
            ->join('product as p', 'p.product_id', 'dsop.product_id')
            ->join('principal as pr', 'pr.principal_id', 'p.principal_id')
            ->join('distributor_invoice AS di', 'di.dist_order_id', '=', 'dso.dist_order_id')
            ->leftJoin('distributor_invoice_line as dil', function ($join) {
                $join->on('dil.di_id', '=', 'di.di_id')
                    ->on('dil.product_id', '=', 'dsop.product_id');
            })
            ->where('dsr.id', $user->getKey())
            ->whereBetween(DB::raw('DATE(dso.order_date)'), [date('Y-m-d', $time), date('Y-m-d', $time2)])
            ->whereNull('dso.deleted_at')
            ->whereNull('di.deleted_at')
            ->whereNull('dil.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pr.deleted_at')
            ->whereNull('dsop.deleted_at');

        $results = $query->get();

        $results->transform(function ($val) {

            $tot_value = $val->dil_qty * $val->dil_unit_price;

            if (isset($val->sales_qty) && isset($val->dil_qty)) {
                $losted_qty = $val->sales_qty - $val->dil_qty;
            }

            if (isset($val->price) && isset($val->dil_unit_price)) {
                $losted_val = $val->price - $tot_value;
            }

            return [
                'date' => date('Y-m-d', strtotime($val->order_date)),
                'dis_name' => $val->dis_name,
                'agency_name' => $val->principal_name,
                'pro_code' => $val->product_code,
                'pro_name' => $val->product_name,
                'pack_size' => 0,
                'sr_code' => $val->dsr_code,
                'sr_name' => $val->dsr_name,
                'order_no' => $val->order_no,
                'order_qty' => isset($val->sales_qty) ? $val->sales_qty : 0,
                'order_value' => isset($val->price) && isset($val->sales_qty) ? number_format($val->sales_qty * $val->price, 2) : 0,
                'order_value_new' => isset($val->price) && isset($val->sales_qty) ? $val->sales_qty * $val->price : 0,
                'inv_qty' => isset($val->dil_qty) ? $val->dil_qty : 0,
                'inv_value' => isset($val->dil_unit_price) && isset($val->dil_qty) ? number_format($tot_value, 2) : 0,
                'inv_value_new' => isset($val->dil_unit_price) && isset($val->dil_qty) ? $tot_value : 0,
                'losed_qty' => isset($losted_qty) ? $losted_qty : 0,
                'losed_value' => isset($losted_val) ? number_format($losted_val, 2) : 0,
                'losed_value_new' => isset($losted_val) ? $losted_val : 0,
            ];
        });

        return view('WebView/Distributor.dis_order_vs_invoice_search', ['orders' => $results]);
    }
}
