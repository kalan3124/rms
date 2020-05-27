<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Principal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AgencyWiseSaleReportController extends ReportController
{
    protected $title = "Agency Wise Sales Report";

    protected $updateColumnsOnSearch = true;

    public function search($request)
    {
        $values = $request->input('values', []);

        $invoiceQuery = DB::table('distributor_invoice as di')
            ->select([
                'di.dsr_id',
                'di.dis_id',
                'di.created_at',
                'dil.dil_unit_price',
                'dil.dil_qty',
                'p.principal_id',
                DB::raw('YEAR(dil.created_at) as year'),
                DB::raw('MONTH(dil.created_at) as month'),
                DB::raw('SUM(dil.dil_qty) as sale_qty'),
                DB::raw('SUM(dil.dil_unit_price * dil.dil_qty) as sale_amount'),
            ])
            ->join('distributor_invoice_line as dil', 'dil.di_id', 'di.di_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->whereNull('di.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
            ->groupBy('p.principal_id', DB::raw('YEAR(dil.created_at)'), DB::raw('MONTH(dil.created_at)'));

        $returnQuery = DB::table('distributor_return as di')
            ->select([
                'di.dsr_id',
                'di.dis_id',
                'di.created_at',
                'dil.dri_price',
                'dil.dri_qty',
                'p.principal_id',
                DB::raw('YEAR(di.return_date) as year'),
                DB::raw('MONTH(di.return_date) as month'),
                DB::raw('0 - SUM(dil.dri_qty) as sale_qty'),
                DB::raw('0 - SUM(dil.dri_price * dil.dri_qty) as sale_amount'),
            ])
            ->join('distributor_return_item as dil', 'dil.dis_return_id', 'di.dis_return_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->whereNull('di.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
            ->groupBy('p.principal_id', DB::raw('YEAR(dil.created_at)'), DB::raw('MONTH(dil.created_at)'));

        if (isset($values['pr_id'])) {
            $invoiceQuery->where('p.principal_id', $values['pr_id']['value']);
            $returnQuery->where('p.principal_id', $values['pr_id']['value']);
        }

        if (isset($values['dsr_id'])) {
            $invoiceQuery->where('di.dsr_id', $values['dsr_id']['value']);
            $returnQuery->where('di.dsr_id', $values['dsr_id']['value']);
        }

        if (isset($values['dis_id'])) {
            $invoiceQuery->where('di.dis_id', $values['dis_id']['value']);
            $returnQuery->where('di.dis_id', $values['dis_id']['value']);
        }

        if (isset($values['s_date']) && isset($values['e_date'])) {
            // $invoiceQuery->whereDate('di.created_at','>=',$values['s_date']);
            // $invoiceQuery->whereDate('di.created_at','<=',$values['e_date']);

            $invoiceQuery->whereBetween(DB::raw('DATE(di.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);

            // $returnQuery->whereDate('di.created_at','>=',$values['s_date']);
            // $returnQuery->whereDate('di.created_at','<=',$values['e_date']);

            $returnQuery->whereBetween(DB::raw('DATE(di.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);

        }

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $invoiceQuery->where('di.dis_id', $user->getKey());
            $returnQuery->where('di.dis_id', $user->getKey());
        }

        $invoices = $invoiceQuery->get();
        $returns = $returnQuery->get();

        $sales = $invoices->concat($returns);

        $query = Principal::whereIn('principal_id', $sales->pluck('principal_id')->all());

        $count = $this->paginateAndCount($query, $request, 'principal_name');

        $results = $query->get();

        $begin = new \DateTime(date('Y-m-01', strtotime($values['s_date'])));
        $end = new \DateTime(date('Y-m-t', strtotime($values['e_date'])));

        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($begin, $interval, $end);

        $total_qty = 0;
        $total_val = 0;
        $results->transform(function ($val) use ($total_qty, $total_val, $sales, $period) {
            $disUser = $sales->where('principal_id', $val->principal_id)->first();
            $dis = User::find($disUser->dis_id);

            foreach ($period as $key => $month) {

                $saleQty = $sales->where('principal_id', $val->principal_id)->where('year', $month->format('Y'))->where('month', $month->format('m'))->sum('sale_qty');
                $sale = $sales->where('principal_id', $val->principal_id)->where('year', $month->format('Y'))->where('month', $month->format('m'))->sum('sale_amount');

                $return['sale_month_' . $month->format('m')] = number_format($sale ? round($sale, 2) : 0, 2);
                $return['unformated_sale_month_' . $month->format('m')] = $sale ? round($sale, 2) : 0;

                $total_qty += $saleQty;
                $total_val += $sale;
            }

            $return['agency_name'] = $val->principal_name;
            $return['dis_name'] = $dis->name;

            $return['total_val_new'] = isset($total_val) ? $total_val : 0;
            $return['total_val'] = number_format($total_val, 2);

            return $return;
        });

        $results = $results->SortByDesc('total_qty')->values();

        $row = [];
        foreach ($period as $key => $month) {
            $row['sale_month_' . $month->format('m')] = number_format($results->sum('unformated_sale_month_' . $month->format('m')), 2);
        }

        $grandtotal = $sales->sum('sale_amount');
        $row['special'] = true;
        $row['agency_name'] = 'Total';
        $row['dis_name'] = NULL;
        $row['total_val'] = number_format($results->sum('total_val_new'), 2);

        $results->push($row);

        $rownew = [];
        $rownew['special'] = true;
        $rownew['agency_name'] = 'Grand Total';
        $rownew['dis_name']=null;
        $rownew['total_val'] = number_format($grandtotal, 2);

        $results->push($rownew);

        return [
            'results' => $results,
            'count' => $count,
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $values = $request->input('values', []);

        $columnController->text('agency_name')->setLabel('Agency Name');
        $columnController->number('dis_name')->setLabel('Distributor');

        if (isset($values['s_date']) && isset($values['e_date'])) {

            $begin = new \DateTime(date('Y-m-01', strtotime($values['s_date'])));
            $end = new \DateTime(date('Y-m-t', strtotime($values['e_date'])));

            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($begin, $interval, $end);

            foreach ($period as $key => $month) {
                // $columnController->number('qty_month_'.$month->format('m'))->setLabel($month->format('M').' Qty');
                $columnController->number('sale_month_' . $month->format('m'))->setLabel($month->format('M') . ' Net sales value');
            }
        }

        $columnController->number('total_val')->setLabel('Total');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')])->setValidations('');
        $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{dis_id}']);
        $inputController->ajax_dropdown('pr_id')->setLabel('Principal')->setLink('principal')->setValidations('');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['dsr_id', 'dis_id', 'pr_id'], ['s_date', 'e_date']]);
    }
}
