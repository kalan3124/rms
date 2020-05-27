<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\DistributorInvoiceLine;
use App\Models\DistributorReturn;
use App\Models\DistributorReturnItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class DsrWiseSalesReportController extends ReportController
{
    protected $title = "Dsr Wise Sales Report";

    public function search($request)
    {
        $values = $request->input('values');

        $invoiceQuery = DB::table('distributor_invoice as di')
            ->select([
                'di.dsr_id',
                'di.dis_id',
                'di.created_at',
                'dil.dil_unit_price',
                'dil.dil_qty',
                'p.product_code',
                'p.product_name',
                'p.product_id',
                DB::raw('SUM(dil.dil_unit_price * dil.dil_qty) as sale_amount')
            ])
            ->join('distributor_invoice_line as dil', 'dil.di_id', 'di.di_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->whereNull('di.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
            ->groupBy('di.dsr_id');

        $returnQuery = DB::table('distributor_return as di')
            ->select([
                'di.dsr_id',
                'di.dis_id',
                'di.created_at',
                'dil.dri_price',
                'dil.dri_qty',
                'p.product_code',
                'p.product_name',
                'p.product_id',
                DB::raw('SUM(dil.dri_price * dil.dri_qty) as sale_return_amount')
            ])
            ->join('distributor_return_item as dil', 'dil.dis_return_id', 'di.dis_return_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->whereNull('di.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
            ->groupBy('di.dsr_id');

        if (isset($values['dsr_id'])) {
            $invoiceQuery->where('di.dsr_id', $values['dsr_id']['value']);
            $returnQuery->where('di.dsr_id', $values['dsr_id']['value']);
        }

        if (isset($values['dis_id'])) {
            $invoiceQuery->where('di.dis_id', $values['dis_id']['value']);
            $returnQuery->where('di.dis_id', $values['dis_id']['value']);
        }

        if (isset($values['s_date']) && isset($values['e_date'])) {
            // $invoiceQuery->whereDate('di.created_at', '>=', $values['s_date']);
            // $invoiceQuery->whereDate('di.created_at', '<=', $values['e_date']);

            $invoiceQuery->whereBetween( DB::raw( 'DATE(di.created_at)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);

            // $returnQuery->whereDate('di.created_at', '>=', $values['s_date']);
            // $returnQuery->whereDate('di.created_at', '<=', $values['e_date']);

            $returnQuery->whereBetween( DB::raw( 'DATE(di.created_at)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);

        }

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $invoiceQuery->where('di.dis_id', $user->getKey());
            $returnQuery->where('di.dis_id', $user->getKey());
        }

        $invoices = $invoiceQuery->get();
        $returns = $returnQuery->get();

        $sales = $invoices->concat($returns);

        $userQuery = User::whereIn('id', $sales->pluck('dsr_id')->all());

        $count = $this->paginateAndCount($userQuery, $request, 'id');

        $results = $userQuery->get();

        $net_sale = 0;
        $results->transform(function ($val) use ($invoices, $returns, $net_sale) {

            $gross = $invoices->where('dsr_id', $val->id)->sum('sale_amount');
            $return = $returns->where('dsr_id', $val->id)->sum('sale_return_amount');

            if (isset($gross) && isset($return))
                $net_sale = $gross - $return;

            return [
                'dsr_code' => $val->u_code ? $val->u_code : Null,
                'dsr_name' => $val->name ? $val->name : Null,
                'gross' => $gross ? number_format($gross, 2) : '0.00',
                'gross_new' => $gross ? $gross : 0,
                'return' => $return ? number_format($return, 2) : '0.00',
                'return_new' => $return ? $return : 0,
                'net_sale' => $net_sale ? number_format($net_sale, 2) : '0.00',
                'net_sale_new' => $net_sale ? $net_sale : 0,
            ];
        });

        $grandgrosstotal= $sales->sum('sale_amount');
        $grandreturntotal = $sales->sum('sale_return_amount');
        $grandtotal = $grandgrosstotal - $grandreturntotal;

        $row = [
            'special' => true,
            'dsr_code' => 'Total',
            'dsr_name' => '',
            'gross' => number_format($results->sum('gross_new'),2),
            'return' => number_format($results->sum('return_new'),2),
            'net_sale' => number_format($results->sum('net_sale_new'),2)
        ];
        $rownew = [
            'special' => true,
            'dsr_code' => 'Grand Total',
            'dsr_name' => '',
            'gross' => number_format($grandgrosstotal,2),
            'return' => number_format($grandreturntotal,2),
            'net_sale' => number_format($grandtotal,2)
        ];

        $results->push($row);
        $results->push($rownew);

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {

        $columnController->text('dsr_code')->setLabel('DSR Code');
        $columnController->text('dsr_name')->setLabel('DSR Name.');
        $columnController->number('gross')->setLabel('Gross Sales');
        $columnController->number('return')->setLabel('Returns');
        $columnController->number('net_sale')->setLabel('Net Sales value ');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')]);
        $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{dis_id}']);
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['dsr_id', 'dis_id'], ['s_date', 'e_date']]);
    }
}
