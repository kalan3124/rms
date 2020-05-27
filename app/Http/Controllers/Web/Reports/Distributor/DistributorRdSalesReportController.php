<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\DistributorInvoiceLine;
use App\Models\DistributorReturn;
use App\Models\DistributorReturnItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class DistributorRdSalesReportController extends ReportController
{
    protected $title = "RD Sales Report";
    protected $updateColumnsOnSearch = true;

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
            ->groupBy('p.product_id','di.dis_id');

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
            ->groupBy('p.product_id','di.dis_id');


        if (isset($values['dis_id'])) {
            $invoiceQuery->where('di.dis_id', $values['dis_id']['value']);
            $returnQuery->where('di.dis_id', $values['dis_id']['value']);
        }

        if (isset($values['pro_id'])) {
            $invoiceQuery->where('p.product_id', $values['pro_id']['value']);
            $returnQuery->where('p.product_id', $values['pro_id']['value']);
        }


        if (isset($values['month'])) {
            $invoiceQuery->whereBetween(DB::raw('DATE(di.created_at)'), [date('Y-m-01', strtotime($values['month'])), date('Y-m-t', strtotime($values['month']))]);
            $returnQuery->whereBetween(DB::raw('DATE(di.created_at)'), [date('Y-m-01', strtotime($values['month'])), date('Y-m-t', strtotime($values['month']))]);
        }


        $invoices = $invoiceQuery->get();
        $returns = $returnQuery->get();

        $sales = $invoices->concat($returns);

        $query = Product::with('principal');
        $query->whereIn('product_id', $sales->pluck('product_id')->all());
        $count = $this->paginateAndCount($query, $request, 'principal_id');

        if (isset($values['pro_id'])) {
            $query->where('product_id', $values['pro_id']['value']);
        }
        $products = $query->get();

        $newResults = [];
        $products->transform(function ($val) use ($invoices, $returns) {

            $query = User::where('u_tp_id', 14);
            $query->where('id', '!=', 585);
            $users = $query->get();

            $return['pri_name'] = $val->principal ? $val->principal->principal_name : '';
            $return['pri_id'] = $val->principal ? $val->principal->principal_id : '';
            $return['pro_code'] = $val->product_code;
            $return['pro_name'] = $val->product_name;

            $total = 0;
            foreach ($users as $key => $row) {
                $gross = $invoices->where('product_id', $val->product_id)->where('dis_id', $row->id)->sum('sale_amount');
                $returnNew = $returns->where('product_id', $val->product_id)->where('dis_id', $row->id)->sum('sale_return_amount');

                if (isset($gross) && isset($return)) {
                    $net_sale = $gross - $returnNew;
                    $return['dis_' . $row->id] = number_format($net_sale, 2);
                    $return['dis_new_' . $row->id] = $net_sale;
                    $total += $net_sale;
                }
            }

            $return['net_sale'] = isset($total) ? number_format($total, 2) : 0;
            $return['net_sale_new'] = isset($total) ? $total : 0;

            return $return;
        });

        $query = User::where('u_tp_id', 14);
        $query->where('id', '!=', 585);
        $users = $query->get();

        $total_dis = 0;
        $dis_tot = 0;
        $dis = [];
        foreach ($products as $key => $value) {
            $newResults[] = $value;

            $total_dis += $value['net_sale_new'];

            // foreach ($users as $keys => $user) {
            //     foreach ($products->where('dis_new_' . $user->id) as $key => $dp) {
            //         $dis_tot += $dp['dis_new_'.$user->id];
            //     }
            //     $dis[$user->id] = $dis_tot;
            // }

            if ($key != count($products) - 1) {
                if (isset($value['pri_id'])) {
                    $perCode = $products[$key + 1];
                    if ($value['pri_id'] != $perCode['pri_id']) {

                        $row['special'] = true;

                        // foreach ($users as $key => $val) {
                        //     $row['dis_' . $val->id] = $dis[$val->id];
                        // }

                        // $row['dis_602'] = $dis[];

                        $row['net_sale'] = number_format($total_dis, 2);
                        $newResults[] = $row;
                        $total_dis = 0;
                        $dis_tot = 0;
                    }
                }
            }
        }

        // return $dis;

        $query = User::where('u_tp_id', 14);
        $query->where('id', '!=', 585);
        $users = $query->get();

        $newRow = [];
        $newRow['special'] = true;
        foreach ($users as $key => $row) {
            // $newRow['dis_' . $row->id] = number_format($products->sum('dis_new_' . $val->id),2);
            $newRow['dis_' . $row->id] = "";
        }

        array_push($newResults, $newRow);

        return [
            'results' => $newResults,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $values = $request->input('values', []);
        $query = User::where('u_tp_id', 14);
        $query->where('id', '!=', 585);

        if (isset($values['dis_id'])) {
            $query->where('id', $values['dis_id']['value']);
        }

        $users = $query->get();

        $columnController->text('pri_name')->setLabel('Principal');
        $columnController->text('pro_code')->setLabel('Product Code');
        $columnController->text('pro_name')->setLabel('Product');

        foreach ($users as $key => $user) {
            $columnController->number('dis_' . $user->id)->setLabel($user->name);
        }

        $columnController->number('net_sale')->setLabel('Total');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type'), 'divi_id' => '{divi_id}']);
        $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{dis_id}']);
        $inputController->ajax_dropdown('divi_id')->setLabel('Division')->setLink('division');
        $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product');
        $inputController->date('month')->setLabel('Month');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['divi_id', 'dis_id'], ['pro_id', 'month']]);
    }
}
