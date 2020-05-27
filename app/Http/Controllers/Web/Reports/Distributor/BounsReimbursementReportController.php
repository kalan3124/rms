<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BounsReimbursementReportController extends ReportController
{
    protected $title = "Bonus Reimbursement Report";

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
                'p.product_code',
                'p.product_name',
                'p.product_id',
                'dil.unit_price_no_tax',
                DB::raw('SUM(dil.dil_qty) as sale_qty'),
                DB::raw('SUM(dil.dil_unit_price * dil.dil_qty) as sale_amount'),
            ])
            ->join('distributor_invoice_line as dil', 'dil.di_id', 'di.di_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->whereNull('di.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
        //   ->groupBy('di.dsr_id');
            ->groupBy('dil.product_id');

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
                'dil.unit_price_no_tax',
                DB::raw('SUM(dil.dri_qty) as sale_return_qty'),
                DB::raw('SUM(dil.dri_price * dil.dri_qty) as sale_return_amount'),
            ])
            ->join('distributor_return_item as dil', 'dil.dis_return_id', 'di.dis_return_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->whereNull('di.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
        //   ->groupBy('di.dsr_id');
            ->groupBy('dil.product_id');

        if (isset($values['dsr_id'])) {
            $invoiceQuery->where('di.dsr_id', $values['dsr_id']['value']);
            $returnQuery->where('di.dsr_id', $values['dsr_id']['value']);
        }

        if (isset($values['dis_id'])) {
            $invoiceQuery->where('di.dis_id', $values['dis_id']['value']);
            $returnQuery->where('di.dis_id', $values['dis_id']['value']);
        }

        if (isset($values['pro_id'])) {
            $invoiceQuery->where('p.product_id', $values['pro_id']['value']);
            $returnQuery->where('p.product_id', $values['pro_id']['value']);
        }

        if (isset($values['s_date']) && isset($values['e_date'])) {
            $invoiceQuery->whereBetween(DB::raw('DATE(di.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
            $returnQuery->whereBetween(DB::raw('DATE(di.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        $invoices = $invoiceQuery->get();
        $returns = $returnQuery->get();

        $sales = $invoices->concat($returns);

        $ProQuery = Product::with('principal')->whereIn('product_id', $sales->pluck('product_id')->all());
        $count = $this->paginateAndCount($ProQuery, $request, 'product_id');

        $results = $ProQuery->get();

        $invoice_bonus = DB::table('distributor_invoice_bonus_line as dibl')
            ->join('distributor_invoice as di', 'di.di_id', 'dibl.di_id')
            ->select([
                DB::raw('SUM(dibl.dibl_qty) as dibl_qty'),
                DB::raw('SUM(dibl.dibl_qty * dibl.dibl_unit_price) as dibl_unit_price'),
                'dibl.product_id',
            ])
            ->whereBetween(DB::raw('DATE(dibl.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))])
            ->whereNull('dibl.deleted_at')
            ->whereNull('di.deleted_at')->groupBy('dibl.product_id');

        $return_bonus = DB::table('distributor_return_bonus_item as drbi')
            ->join('distributor_return as dr', 'dr.dis_return_id', 'drbi.dis_return_id')
            ->select([
                DB::raw('SUM(drbi.drbi_qty) as drbi_qty'),
                'drbi.product_id',
            ])
            ->whereBetween(DB::raw('DATE(drbi.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))])
            ->whereNull('drbi.deleted_at')
            ->whereNull('dr.deleted_at')->groupBy('drbi.product_id');

        if (isset($values['dsr_id'])) {
            $invoice_bonus->where('di.dsr_id', $values['dsr_id']['value']);
            $return_bonus->where('dr.dsr_id', $values['dsr_id']['value']);
        }

        if (isset($values['dis_id'])) {
            $invoice_bonus->where('di.dis_id', $values['dis_id']['value']);
            $return_bonus->where('dr.dis_id', $values['dis_id']['value']);
        }

        if (isset($values['pro_id'])) {
            $invoice_bonus->where('dibl.product_id', $values['pro_id']['value']);
            $return_bonus->where('drbi.product_id', $values['pro_id']['value']);
        }

        $invoice_bonuss = $invoice_bonus->get();
        $return_bonuss = $return_bonus->get();

        $results->transform(function ($val) use ($invoices, $returns, $invoice_bonuss, $return_bonuss) {
            $brand = Brand::where('brand_id', $val->brand_id)->first();

            $gross_qty = $invoices->where('product_id', $val->product_id)->sum('sale_qty');
            $return_qty = $returns->where('product_id', $val->product_id)->sum('sale_return_qty');

            $gross = $invoices->where('product_id', $val->product_id)->sum('sale_amount');
            $return = $returns->where('product_id', $val->product_id)->sum('sale_return_amount');

            $grossvat = $invoices->where('product_id', $val->product_id)->first();
            $returnvat = $returns->where('product_id', $val->product_id)->first();

            $bonus_qty = $invoice_bonuss->where('product_id', $val->product_id)->sum('dibl_qty');
            $return_bonus_qty = $return_bonuss->where('product_id', $val->product_id)->sum('drbi_qty');
            $bonus_amount = $invoice_bonuss->where('product_id', $val->product_id)->sum('dibl_unit_price');
            
            $net_vat = 0;
            if (isset($gross_qty) && isset($return_qty)) {
                $net_qty = $gross_qty - $return_qty;
            }

            if (isset($gross) && isset($return)) {
                $net_amount = $gross - $return;
            }

            if (isset($grossvat->unit_price_no_tax) && isset($returnvat->unit_price_no_tax)) {
                $net_vat = $grossvat->unit_price_no_tax - $returnvat->unit_price_no_tax;
            }

            if (isset($bonus_qty) && isset($return_bonus_qty)) {
                $free_qty = $bonus_qty - $return_bonus_qty;
            }

            return [
                'agency_name' => $val->principal->principal_name,
                'pro_code' => $val->product_code,
                'pro_name' => $val->product_name,
                'brand_name' => isset($brand->brand_name) ? $brand->brand_name : '-',
                'pack_size' => $val->pack_size,

                'gross_sold_qty' => isset($gross_qty) ? $gross_qty : 0,
                'gross_val_plus' => isset($gross) ? number_format($gross, 2) : 0,
                'gross_val_plus_new' => isset($gross) ? $gross : 0,
                'gross_val' => isset($grossvat->unit_price_no_tax) ? number_format($grossvat->unit_price_no_tax, 2) : 0,
                'gross_val_new' => isset($grossvat->unit_price_no_tax) ? $grossvat->unit_price_no_tax : 0,

                'return_sold_qty' => isset($return_qty) ? $return_qty : 0,
                'return_val_plus' => isset($return) ? number_format($return, 2) : 0,
                'return_val_plus_new' => isset($return) ? $return : 0,
                'return_val' => isset($returnvat->unit_price_no_tax) ? number_format($returnvat->unit_price_no_tax, 2) : 0,
                'return_val_new' => isset($returnvat->unit_price_no_tax) ? $returnvat->unit_price_no_tax : 0,

                'net_sold_qty' => isset($net_qty) ? $net_qty : 0,
                'net_val_plus' => isset($net_amount) ? number_format($net_amount, 2) : 0,
                'net_val_plus_new' => isset($net_amount) ? $net_amount : 0,
                'net_val' => number_format($net_vat, 2),
                'net_val_new' => $net_vat,

                'bouns_sold_qty' => isset($free_qty) ? $free_qty : 0,
                'bouns_val_plus' => isset($bonus_amount) ? number_format($bonus_amount, 2) : 0,
                //  'bouns_val_plus_new' => isset($bonus_amount) ? $bonus_amount : 0,
                'bouns_val' => 0,
            ];
        });

        $row = [
            'special' => true,
            'agency_name' => null,
            'pro_code' => null,
            'pro_name' => null,
            'brand_name' => null,
            'pack_size' => null,

            'gross_sold_qty' => $results->sum('gross_sold_qty'),
            'gross_val_plus' => number_format($results->sum('gross_val_plus_new'), 2),
            'gross_val' => number_format($results->sum('gross_val_new'), 2),

            'return_sold_qty' => $results->sum('return_sold_qty'),
            'return_val_plus' => number_format($results->sum('return_val_plus_new'), 2),
            'return_val' => number_format($results->sum('return_val_new'), 2),

            'net_sold_qty' => $results->sum('net_sold_qty'),
            'net_val_plus' => number_format($results->sum('net_val_plus_new'), 2),
            'net_val' => number_format($results->sum('net_val_new'), 2),

            'bouns_sold_qty' => $results->sum('bouns_sold_qty'),
            'bouns_val_plus' => number_format($results->sum('bouns_val_plus_new'), 2),
            'bouns_val' => 0,
        ];

        $results->push($row);

        return [
            'results' => $results,
            'count' => $count,
        ];
    }

    protected function getAdditionalHeaders($request)
    {

        $columns = [[
            [
                "title" => "",
                "colSpan" => 4,
            ],
            [
                "title" => "Gross Sales Value",
                "colSpan" => 3,
            ],
            [
                "title" => "Returns‎",
                "colSpan" => 3,
            ],
            [
                "title" => "Net Value",
                "colSpan" => 3,
            ],
            [
                "title" => "",
                "colSpan" => 4,
            ],
        ]];

        return $columns;
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $values = $request->input('values', []);

        $columnController->text('agency_name')->setLabel('Agency');
        $columnController->text('pro_code')->setLabel('Product Code');
        $columnController->text('pro_name')->setLabel('Product Name');
        $columnController->text('brand_name')->setLabel('Brand Name');
        $columnController->text('pack_size')->setLabel('Pack Size');

        $columnController->text('gross_sold_qty')->setLabel('Qty Sold');
        $columnController->text('gross_val_plus')->setLabel('Value + Vat');
        $columnController->text('gross_val')->setLabel('Value');

        $columnController->text('return_sold_qty')->setLabel('Return Qty');
        $columnController->text('return_val_plus')->setLabel('Value + Vat');
        $columnController->text('return_val')->setLabel('Value');

        $columnController->text('net_sold_qty')->setLabel('Qty Sold');
        $columnController->text('net_val_plus')->setLabel('Value + Vat');
        $columnController->text('net_val')->setLabel('Value');

        $columnController->text('bouns_sold_qty')->setLabel('Bonus Qty');
        $columnController->text('bouns_val_plus')->setLabel('Bonus Value');
        $columnController->text('bouns_val')->setLabel('Net Disc');

    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')])->setValidations('');
        $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product')->setValidations('');
        $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{dis_id}'])->setValidations('');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['dsr_id', 'dis_id', 'pro_id'], ['s_date', 'e_date']]);
    }
}
