<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\User;
use App\Models\Area;

class OrderVsInvoiceReportController extends ReportController
{
    protected $title = "Order Vs Invoice Report";

    public function search(Request $request)
    {
        $values = $request->input('values');

        $query = DB::table('sfa_sales_order as sso')
            ->select([
                'sso.order_no',
                'sso.u_id',
                'c.chemist_id',
                'ar.ar_id',
                'c.chemist_code',
                'c.chemist_name',
                'st.sub_twn_code',
                'sso.created_at',
                'ssop.price',
                'sso.sales_order_amt',
                DB::raw('SUM(ssop.price * ssop.sales_qty) as amount')
            ])
            ->join('sfa_sales_order_product as ssop', 'ssop.order_id', 'sso.order_id')
            ->join('chemist as c', 'c.chemist_id', 'sso.chemist_id')
            ->join('sub_town as st', 'st.sub_twn_id', 'c.sub_twn_id')
            ->join('area as ar', 'ar.ar_id', 'sso.ar_id')
            ->where('order_date', '>=', $values['s_date'])
            ->where('order_date', '<=', $values['e_date'])
            ->whereNull('ssop.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('st.deleted_at')
            ->whereNull('sso.deleted_at')
            ->groupBy('sso.order_no');

        $grandsfaorder = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('amount'));


        if (isset($values['user'])) {
            $query->where('sso.u_id', $values['user']['value']);
        }

        if (isset($values['chem_id'])) {
            $query->where('c.chemist_id', $values['chem_id']['value']);
        }

        if (isset($values['s_date']) && isset($values['e_date'])) {
            $query->whereDate('order_date', '>=', $values['s_date']);
            $query->whereDate('order_date', '<=', $values['e_date']);
        }

        if (isset($values['area'])) {
            $query->where('sso.ar_id', $values['area']['value']);
        }

        $count = $this->paginateAndCount($query, $request, 'order_no');
        $results = $query->get();

        $formattedResults = [];

        foreach ($results as $key => $val) {
            if (isset($val->order_no)) {
                $invoice = Invoice::where('customer_po_no', $val->order_no)->first();

                if (isset($invoice->invoice_no))
                    // {
                    // $invoice_line = InvoiceLine::where('invoice_no',$invoice->invoice_no)->where('series_id',$invoice->invoice_series)->select('invoice_date','series_id',DB::raw('SUM(net_curr_amount) as inv_amount'))->first();
                    $invoice_line = DB::table('invoice_line as il')
                        ->leftJoin('latest_price_informations AS pi',function($query){
                            // $query->on('pi.product_id','=','p.product_id');
                            $query->on('pi.product_id','=','il.product_id');
                            $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                                    })
                        ->select([
                            'pi.lpi_pg01_sales',
                            'pi.lpi_bdgt_sales',
                            'il.invoice_date',
                            'il.invoiced_qty',
                            // 'il.sale_unit_price',
                            DB::raw('SUM(il.sale_unit_price * il.invoiced_qty) as sale_unit_price')
                            // DB::raw('ROUND(IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * Ifnull(Sum(il.invoiced_qty), 0),2) AS inv_amount'),
                        ])
                        ->where('il.invoice_no', $invoice->invoice_no)
                        ->where('il.series_id', $invoice->invoice_series)
                        // $invoice_line->groupBy('il.product_id');
                        ->first();

                // return $invoice_line;
                // }

            }

            $diff = 0;
            if (isset($val->amount) && isset($invoice_line->sale_unit_price))
                $diff = $val->amount - $invoice_line->sale_unit_price;

            $sale_man = User::find($val->u_id);
            $arr2 = str_split($sale_man->u_code, 4);
            $area = Area::where('ar_code', $arr2[0])->first();
            $formattedResults[] = [
                'ar_code' => $area->ar_code,
                'sale_man' => isset($sale_man->name) ? $sale_man->name : "",
                'cus_code' => $val->chemist_code,
                'cus_name' => $val->chemist_name,
                'order_no' => $val->order_no,
                'order_create_date' => $val->created_at,
                'order_val' => isset($val->amount) ? number_format($val->amount, 2) : 0,
                'order_val_new' => $val->amount,
                'inv_no' => isset($invoice->invoice_no) ? $invoice->invoice_no : "-",
                'inv_date' => isset($invoice_line->invoice_date) ? $invoice_line->invoice_date : "-",
                'inv_val' => isset($invoice_line->sale_unit_price) ? number_format($invoice_line->sale_unit_price, 2) : 0,
                'inv_val_new' => isset($invoice_line->sale_unit_price) ? $invoice_line->sale_unit_price : 0,
                'diff' => number_format($diff, 2)
            ];
        }

        $results = collect($formattedResults);

        $newRow = [];
        $newRow  = [
            'special' => true,
            'ar_code' => 'Total',
            'sale_man' => NULL,
            'cus_code' => NULL,
            'cus_name' => NULL,
            'order_no' => NULL,
            'order_create_date' => NULL,
            'order_val' => number_format($results->sum('order_val_new'), 2),
            'inv_no' => NULL,
            'inv_date' => NULL,
            'inv_val' => number_format($results->sum('inv_val_new'), 2),
            'diff' => NULL,

        ];

        $formattedResults[] = $newRow;

        $newRownew = [];
        $newRownew  = [
            'special' => true,
            'ar_code' => 'Grand Total',
            'sale_man' => NULL,
            'cus_code' => NULL,
            'cus_name' => NULL,
            'order_no' => NULL,
            'order_create_date' => NULL,
            'order_val' => number_format($grandsfaorder, 2),
            'inv_no' => NULL,
            'inv_date' => NULL,
            'inv_val' => NULL,
            'diff' => NULL,

        ];

        $formattedResults[] = $newRownew;

        return [
            'results' => $formattedResults,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('ar_code')->setLabel('Territory Code');
        $columnController->text('sale_man')->setLabel('Salesman');
        $columnController->text('cus_code')->setLabel('Customer Code');
        $columnController->text('cus_name')->setLabel('Customer Name');

        $columnController->text('order_no')->setLabel('SFA Order No');
        $columnController->text('order_create_date')->setLabel('SFA Order Create Date');
        $columnController->number('order_val')->setLabel('SFA Order Value');
        $columnController->text('inv_no')->setLabel('IFS Invoice No');
        $columnController->text('inv_date')->setLabel('IFS Invoice Date');
        $columnController->number('inv_val')->setLabel('IFS Invoice Value');
        $columnController->number('diff')->setLabel('Difference');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type')])->setValidations('');
        $inputController->ajax_dropdown('chem_id')->setLabel('Chemist')->setLink('chemist')->setValidations('');
        $inputController->ajax_dropdown('area')->setLabel('Area')->setLink('area')->setValidations('');
        $inputController->date('s_date')->setLabel('From')->setLink('s_date');
        $inputController->date('e_date')->setLabel('To')->setLink('e_date');

        $inputController->setStructure([
            ['user', 'chem_id', 'area'], ['s_date', 'e_date']
        ]);
    }
}
