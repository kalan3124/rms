<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Product;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class YtdUnitWiseSlaeReportController extends ReportController
{
    protected $title = "YTD Unit Wise Sales Report";

    protected $updateColumnsOnSearch = true;

    public function search(Request $request)
    {
        $values = $request->input('values');

        $begin = new \DateTime(date('Y-m-01', strtotime($values['s_date'])));
        $end = new \DateTime(date('Y-m-d', strtotime($values['e_date'])));

        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($begin, $interval, $end);

        $invoice = DB::table('product as p')
            ->join('invoice_line as il', 'il.product_id', 'p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'il.product_id',
                DB::raw('YEAR(il.invoice_date) as year'),
                DB::raw('MONTH(il.invoice_date) as month'),
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('il.product_id', DB::raw('YEAR(il.invoice_date)'), DB::raw('MONTH(il.invoice_date)'));

        $return = DB::table('product as p')
            ->join('return_lines as il', 'il.product_id', 'p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'il.product_id',
                DB::raw('YEAR(il.invoice_date) as year'),
                DB::raw('MONTH(il.invoice_date) as month'),
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS return_qty'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('il.product_id', DB::raw('YEAR(il.invoice_date)'), DB::raw('MONTH(il.invoice_date)'));

        if (isset($values['user'])) {
            $products = SalesmanValidPart::where('u_id', $values['user']['value'])->whereDate('from_date', '<=', date('Y-m-d', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-d', strtotime($values['e_date'])))->get();
            $chemists = SalesmanValidCustomer::where('u_id', $values['user']['value'])->whereDate('from_date', '<=', date('Y-m-d', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-d', strtotime($values['e_date'])))->get();

            $invoice->whereIn('il.product_id', $products->pluck('product_id')->all());
            $invoice->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

            $return->whereIn('il.product_id', $products->pluck('product_id')->all());
            $return->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

        }

        if (isset($values['pro_id'])) {
            $invoice->where('il.product_id', $values['pro_id']['value']);
            $return->where('il.product_id', $values['pro_id']['value']);
        }

        if (isset($values['s_date'])) {
            $invoice->whereDate('il.invoice_date', '>=', date('Y-m-d', strtotime($values['s_date'])));
            $invoice->whereDate('il.invoice_date', '<=', date('Y-m-d', strtotime($values['e_date'])));

            $return->whereDate('il.invoice_date', '>=', date('Y-m-d', strtotime($values['s_date'])));
            $return->whereDate('il.invoice_date', '<=', date('Y-m-d', strtotime($values['e_date'])));
        }

        $invoices = $invoice->get();
        $returns = $return->get();

        $allachivements = $invoices->concat($returns);

        $product = Product::with('principal')->whereIn('product_id', $allachivements->pluck('product_id')->all());
        $count = $this->paginateAndCount($product, $request, 'product_id');

        $results = $product->get();

        $productAchi = 0;
        $results->transform(function ($row, $key) use ($invoices, $returns, $period, $productAchi, $results) {

            $return['pro_code'] = isset($row->product_code) ? $row->product_code : null;
            $return['pri_name'] = $row->principal ? $row->principal->principal_name : '-';
            $return['pro_name'] = $row->product_name;

            foreach ($period as $key => $month) {
                $salesAchi = $invoices->where('product_id', $row->product_id)->where('year', $month->format('Y'))->where('month', $month->format('m'))->sum('net_qty');
                $returnAchi = $returns->where('product_id', $row->product_id)->where('year', $month->format('Y'))->where('month', $month->format('m'))->sum('return_qty');

                if (isset($salesAchi) && isset($returnAchi)) {
                    $productAchi = $salesAchi - $returnAchi;
                }

                $return['month_' . $month->format('m')] = $productAchi;
                $return['month_new' . $month->format('m')] = $productAchi;
            }

            return $return;
        });

        $row = [];

        $row = [
            'special' => true,
            'pro_code' => 'Total',
            'pri_name' => null,
            'pro_name' => null,
        ];
        foreach ($period as $key => $month) {
            $row['month_' . $month->format('m')] = number_format($results->sum('month_new' . $month->format('m')), 2);
        }

        $results->push($row);

        return [
            'results' => $results,
            'count' => $count,
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $values = $request->input('values', []);

        $columnController->text('pro_code')->setLabel('Product Code');
        $columnController->text('pri_name')->setLabel('Principal');
        $columnController->text('pro_name')->setLabel('Product');

        if (isset($values['s_date']) && isset($values['e_date'])) {

            $begin = new \DateTime(date('Y-m-01', strtotime($values['s_date'])));
            $end = new \DateTime(date('Y-m-d', strtotime($values['e_date'])));

            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($begin, $interval, $end);

            foreach ($period as $key => $month) {
                $columnController->number('month_' . $month->format('m'))->setLabel($month->format('M'));
            }
        }
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product')->setValidations('');
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type')])->setValidations('');
        $inputController->date('s_date')->setLabel('From')->setLink('s_date');
        $inputController->date('e_date')->setLabel('To')->setLink('e_date');

        $inputController->setStructure([
            ['user', 'pro_id'], ['s_date', 'e_date'],
        ]);
    }
}
