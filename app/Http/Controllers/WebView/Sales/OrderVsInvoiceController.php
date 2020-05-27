<?php
namespace App\Http\Controllers\WebView\Sales;

use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

class OrderVsInvoiceController extends Controller{
     public function index(){
          return view('WebView/Sales.order_vs_invoice');
     }
     public function getOrderVsInvoice(Request $request){

          $validation = Validator::make($request->all(),[
               'date_month'=>'required'
          ]);

          $time = time();

          if(!$validation->fails())
               $time = strtotime($request->input('date_month'));
               $time2 = strtotime($request->input('date_month2'));

          $user= Auth::user();

          $query = DB::table('sfa_sales_order as sso')
                    ->select([
                         'sso.order_no',
                         'sso.u_id',
                         'c.chemist_code',
                         'c.chemist_name',
                         'st.sub_twn_code',
                         'sso.created_at',
                         'ssop.price',
                         'sso.sales_order_amt',
                         DB::raw('SUM(ssop.price * ssop.sales_qty) as amount')
                    ])
                    ->join('sfa_sales_order_product as ssop','ssop.order_id','sso.order_id')
                    ->join('chemist as c','c.chemist_id','sso.chemist_id')
                    ->join('sub_town as st','st.sub_twn_id','c.sub_twn_id')
                    ->where('sso.u_id',$user->getKey())
                    ->whereDate('order_date','>=',date('Y-m-d',$time))
                    ->whereDate('order_date','<=',date('Y-m-d',$time2))
                    ->whereNull('ssop.deleted_at')
                    ->whereNull('c.deleted_at')
                    ->whereNull('st.deleted_at')
                    ->whereNull('sso.deleted_at')
                    ->groupBy('sso.order_no');

                    $results = $query->get();
                    // return $results;

          $formattedResults = [];

          foreach ($results as $key => $val) {
               if(isset($val->order_no)){
                    $invoice = Invoice::where('customer_po_no',$val->order_no)->first();

                    if(isset($invoice->invoice_no))
                    // {
                         // $invoice_line = InvoiceLine::where('invoice_no',$invoice->invoice_no)->where('series_id',$invoice->invoice_series)->select('invoice_date','series_id',DB::raw('SUM(net_curr_amount) as inv_amount'))->first();
                         $invoice_line = DB::table('invoice_line as il')
                         ->leftJoin('latest_price_informations AS pi',function($query){
                             $query->on('pi.product_id','=','il.product_id');
                             $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                                      })
                         ->select([
                              'pi.lpi_pg01_sales',
                              'pi.lpi_bdgt_sales',
                              'il.invoice_date',
                              'il.invoiced_qty',
                              DB::raw('SUM(il.sale_unit_price * il.invoiced_qty) as inv_amount'),
                              // DB::raw('ROUND(IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * Ifnull(Sum(il.invoiced_qty), 0),2) AS inv_amount'),
                         ])
                         ->where('il.invoice_no',$invoice->invoice_no)
                         ->where('il.series_id',$invoice->invoice_series)
                         // $invoice_line->groupBy('il.product_id');
                         ->first();

                         // return $invoice_line;
                    // }

               }

               $sale_man = User::find($val->u_id);

               $diff = 0;
               if(isset($val->amount) && isset($invoice_line->inv_amount))
                    $diff = $val->amount - $invoice_line->inv_amount;

               $formattedResults [] = [
                    'ar_code' => $val->sub_twn_code,
                    'sale_man' => isset($sale_man->name)?$sale_man->name:"",
                    'cus_code' => $val->chemist_code,
                    'cus_name' => $val->chemist_name,
                    'order_no' => $val->order_no,
                    'order_create_date' => $val->created_at,
                    'order_val' => isset($val->sales_order_amt)?number_format($val->sales_order_amt,2):0,
                    'inv_no' => isset($invoice->invoice_no)?$invoice->invoice_no:"-",
                    'inv_date' => isset($invoice_line->invoice_date)?$invoice_line->invoice_date:"-",
                    'inv_val' => isset($invoice_line->inv_amount)?number_format($invoice_line->inv_amount,2):"-",
                    'diff' => number_format($diff,2)
               ];
          }

          return view('WebView/Sales.order_vs_invoice_search',['orders' => $formattedResults]);
     }
}

?>
