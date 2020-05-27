<?php

namespace App\Http\Controllers;
use App\Ext\Get\InvoiceHead;
use App\Ext\Get\SalesPart;
use App\Http\Controllers\Controller;
use App\Ext\Get\SalesPriceList;
use App\Ext\Get\InvoiceLines;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Models\User;
use App\Traits\Territory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    use Territory;


    public function subTowns(Request $request)
    {
        $user_id = $request->input('u_id');

        $subTowns =  $this->getAllocatedTerritories(User::find($user_id));

        echo implode(',',$subTowns->pluck('sub_twn_id')->all());
    }


    public function products(Request $request)
    {
        $user_id = $request->input('u_id');

        $products =  Product::getByUser(User::find($user_id));

        echo implode(',',$products->pluck('product_id')->all());
    }

    public function salesQuery(Request $request){
        $salesQuery = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),DB::raw('sau.id'))
            ->join('product AS p','il.product_id','=','p.product_id')
            ->join('chemist AS c','c.chemist_id','il.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'sau.id',
                'st.sub_twn_id',
                'il.identity',
                'il.product_id',
                'p.product_code',
                'p.product_name',
                'p.principal_id',
                'c.chemist_id',
                InvoiceLine::salesQtyColumn('gross_qty',false,false),
                InvoiceLine::salesQtyColumn('net_qty',false,false),
                InvoiceLine::salesAmountColumn('bdgt_value'),
                DB::raw('0 AS return_qty'),
                DB::raw('0 AS rt_bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price'),
                DB::raw('DAY(il.invoice_date) AS day')
            ]),'c.sub_twn_id',[0,0])
            ->where('sau.id',0)
            ->whereIn('il.product_id',[0,0])
            ->whereDate('il.invoice_date','<=',date('Y-m-t'))
            ->whereDate('il.invoice_date','>=',date('Y-m-01'))
            ->groupBy('il.product_id','st.sub_twn_id','c.chemist_id',DB::raw('DATE(il.invoice_date)'))
            ->toSql();

        echo $salesQuery;
    }


    public function oracleTest()
    {
        $data = SalesPart::limit(20)->get();

        return $data;
    }

    public function getInvoiceHeaders(){

        // Create connection to Oracle, change HOST IP and SID string!
        $db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.2.11)(PORT = 1524)))(CONNECT_DATA=(SID=TEST)))";
        // Enter here your username (DBUSER) and password!
        $conn = oci_connect("INTG", "INTG",$db);
        if (!$conn) {
        $m = oci_error();
        echo $m['message']. PHP_EOL;
        exit;
        }
        else {
        print "Oracle database connection online". PHP_EOL;
        }

        $s = oci_parse($conn, "SELECT COUNT(*) invoice_line_count, to_char(last_updated_on, 'YYYY-MM-DD') invoice_date, SUM(invoiced_qty) invoice_qty FROM ifsapp.EXT_INVOICE_LINES_UIV WHERE to_char(last_updated_on, 'YYYY-MM') = '6' AND to_char(last_updated_on, 'YYYY')='2019' group by to_char(last_updated_on, 'YYYY-MM-DD')");

        oci_execute($s);
        oci_fetch_all($s, $res);
        return $res;
        // echo "<pre>\n";
        // var_dump($res);
        // echo "</pre>\n";

    }

    public function getInvLines(){

        $invoices = InvoiceLines::where('last_updated_on','>','2019-07-20 00:00:00')->get();
        return $invoices;
    }

    public function emailViews(){

        return view("emails.itinerary-changed");
    }
}
