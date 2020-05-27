<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\DistributorCustomer;
use App\Models\DistributorInvoice;
use App\Models\DistributorInvoiceBonusLine;
use App\Models\DistributorInvoiceLine;
use App\Models\SalesPriceLists;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF;

class DistributorInvoiceReportController extends ReportController
{
    protected $title = "Invoice Report";

    public function search($request)
    {

        $values = $request->input('values');

        $invoiceQuery = DB::table('distributor_invoice AS i')
            ->leftJoin('distributor_sales_order AS so', 'i.dist_order_id', '=', 'so.dist_order_id')
            ->join('users AS dsr', 'dsr.id', '=', 'i.dsr_id')
            ->join('users AS dis', 'dis.id', '=', 'i.dis_id')
            ->join('distributor_customer AS dc', 'i.dc_id', '=', 'dc.dc_id')
            ->select([
                'dsr.id AS dsr_id',
                'dis.id AS dis_id',
                'dsr.name AS dsr_name',
                'dis.name AS dis_name',
                'so.order_no',
                'i.di_number',
                'i.di_discount',
                'i.di_amount',
                'i.created_at',
                'i.di_id',
                'i.di_vat_percentage',
                'dc.dc_id',
                'dc.dc_name',
                'i.di_printed_at',
                'i.payment_status',
            ]);

        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'inv_number':
                $sortBy = 'i.di_number';
                break;
            case 'inv_amount':
                $sortBy = 'i.di_amount';
                break;
            case 'created_at':
                $sortBy = 'i.created_at';
                break;
            case 'so_number':
                $sortBy = 'so.order_no';
                break;
            default:
                $sortBy = 'i.created_at';
                break;
        }

        $soNumber = $request->input('values.so_number');
        $invNumber = $request->input('values.inv_number');
        $disId = $request->input('values.dis_id');
        $dsrId = $request->input('values.dsr_id');
        $dcId = $request->input('values.dc_id');
        // $startDate = $request->input('values.s_date');
        // $endDate = $request->input('values.e_date');

        if ($soNumber) {
            $invoiceQuery->where('so.order_no', $soNumber);
        }

        if ($invNumber) {
            $invoiceQuery->where('i.di_number', $invNumber);
        }

        if ($disId) {
            $invoiceQuery->where('dis.id', $disId['value']);
        }

        if ($dsrId) {
            $invoiceQuery->where('dsr.id', $dsrId['value']);
        }

        if ($dcId) {
            $invoiceQuery->where('dc.dc_id', $dcId['value']);
        }

        $grandtot = DB::table(DB::raw("({$invoiceQuery->toSql()}) as sub"))
            ->mergeBindings(get_class($invoiceQuery) == 'Illuminate\Database\Eloquent\Builder' ? $invoiceQuery->getQuery() : $invoiceQuery)->sum(DB::raw('di_amount-di_discount'));

        $granddistot = DB::table(DB::raw("({$invoiceQuery->toSql()}) as sub"))
            ->mergeBindings(get_class($invoiceQuery) == 'Illuminate\Database\Eloquent\Builder' ? $invoiceQuery->getQuery() : $invoiceQuery)->sum(DB::raw('di_discount'));

        $count = $this->paginateAndCount($invoiceQuery, $request, $sortBy);

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $invoiceQuery->where('dis.id', $user->getKey());
        }

        // if ($startDate && $endDate) {
        //     $invoiceQuery->whereDate('i.created_at','>=', $startDate);
        //     $invoiceQuery->whereDate('i.created_at','<=', $endDate);
        // }

        if (isset($values['s_date']) && isset($values['e_date'])) {
            // $query->whereDate('order_date', '>=', $request->input('values.s_date', date('Y-m-d')));
            // $query->whereDate('order_date', '<=', $request->input('values.e_date', date('Y-m-d')));
            $invoiceQuery->whereBetween(DB::raw('DATE(i.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        $invoices = $invoiceQuery->get();

        $invoices->transform(function ($invoice) {

            $color = [];

            if (isset($invoice->di_printed_at)) {
                $color = [
                    'background' => '#3ac280',
                    'border' => '1px solid #fff',
                ];
            } else {
                $color = [
                    'background' => '#eb4680',
                    'border' => '1px solid #fff',
                ];
            }

            $invoiceLines = DistributorInvoiceLine::where('di_id', $invoice->di_id)->with(['product', 'batch'])->withTrashed()->get();

            $invoiceLines->transform(function (DistributorInvoiceLine $invoiceLine) use ($invoice) {

                return [
                    'product' => $invoiceLine->product ? [
                        'label' => $invoiceLine->product->product_name,
                        'value' => $invoiceLine->product->product_id,
                    ] : [
                        'label' => 'DELETED',
                        'value' => 0,
                    ],
                    'batch' => $invoiceLine->batch ? [
                        'label' => $invoiceLine->batch->db_code,
                        'value' => $invoiceLine->batch->db_id,
                    ] : [
                        'label' => 'DELETED',
                        'value' => 0,
                    ],

                    'price' => $invoiceLine->dil_unit_price,
                    'vatPercentage' => $invoice->di_vat_percentage,
                    'qty' => $invoiceLine->dil_qty,
                    'amount' => number_format($invoiceLine->dil_qty * $invoiceLine->dil_unit_price, 2),
                    'raw_amount' => $invoiceLine->dil_qty * $invoiceLine->dil_unit_price,
                    'discountPercent' => $invoiceLine->dil_discount_percent,
                ];
            });

            return [
                'dis_id_style' => $color,
                'dis_id' => [
                    'value' => $invoice->dis_id,
                    'label' => $invoice->dis_name,
                ],
                'dsr_id_style' => $color,
                'dsr_id' => [
                    'value' => $invoice->dsr_id,
                    'label' => $invoice->dsr_name,
                ],
                'dc_id_style' => $color,
                'dc_id' => [
                    'value' => $invoice->dc_id,
                    'label' => $invoice->dc_name,
                ],
                'so_number_style' => $color,
                'so_number' => $invoice->order_no,
                'inv_number_style' => $color,
                'inv_number' => $invoice->di_number,
                'inv_discount_style' => $color,
                'inv_discount' => number_format($invoice->di_discount, 2),
                'inv_discount_new' => $invoice->di_discount,
                'inv_amount_style' => $color,
                'inv_amount' => number_format($invoiceLines->sum('raw_amount'), 2),
                'inv_amount_new' => $invoiceLines->sum('raw_amount'),
                'created_at_style' => $color,
                'created_at' => $invoice->created_at,
                'details_style' => $color,
                'details' => [
                    'title' => $invoice->di_number,
                    'products' => $invoiceLines,
                ],
                'print_button_style' => $color,
                'print_button' => $invoice->di_id,
                'payment_link_style' => $color,
                'payment_link'=> $invoice->payment_status ? null :[
                    'label' => "Paymnet",
                    'link' => '/distributor/payment/invoice_payment/' . str_replace('/', '_', $invoice->di_number),
                    'react' => true
                ],
            ];
        });

        $newRow = [];
        $newRow = [
            'special' => true,
            'dis_id' => [
                'value' => 0,
                'label' => 'Total',
            ],
            'dsr_id' => null,
            'dc_id' => null,
            'so_number' => null,
            'inv_number' => null,
            'inv_amount' => number_format($invoices->sum('inv_amount_new'), 2),
            'inv_discount' => number_format($invoices->sum('inv_discount_new'), 2),
            'created_at' => null,
            'details' => null,
            'print_button' => null,
            'payment_link' => NULL
        ];

        $newRownew = [
            'special' => true,
            'dis_id' => [
                'value' => 0,
                'label' => 'Grand Total',
            ],
            'dsr_id' => null,
            'dc_id' => null,
            'so_number' => null,
            'inv_number' => null,
            'inv_amount' => number_format($grandtot, 2),
            'inv_discount' => number_format($granddistot, 2),
            'created_at' => null,
            'details' => null,
            'print_button' => null,
            'payment_link' => NULL
        ];

        $invoices->push($newRow);
        $invoices->push($newRownew);

        return [
            'results' => $invoices,
            'count' => $count,
        ];
    }

    protected function getAdditionalHeaders($request)
    {

        $columns = [[
            [
                "title" => "Green: Printed Invoice | Red: Not Printed Invoice",
                "colSpan" => 11,
            ],
        ]];

        return $columns;
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {

        $columnController->ajax_dropdown('dis_id')->setLabel('Distributor');
        $columnController->ajax_dropdown('dsr_id')->setLabel('Distributor Sales Rep.');
        $columnController->ajax_dropdown('dc_id')->setLabel('Distributor Customer');
        $columnController->text('so_number')->setLabel('SO Order No.');
        $columnController->text('inv_number')->setLabel('Invoice Order No.');
        $columnController->number('inv_amount')->setLabel('Amount');
        $columnController->number('inv_discount')->setLabel('Discount');
        $columnController->text('created_at')->setLabel('Created Time');
        $columnController->custom("details")->setLabel("Products")->setComponent('InvoiceDetails');
        $columnController->button('print_button')->setLabel('Print')->setLink('report/invoice/print');
        $columnController->link('payment_link')->setLabel('Payment');
    }

    public function setInputs($inputController)
    {
        $inputController->text('so_number')->setLabel('So No.')->setValidations('');
        $inputController->text('inv_number')->setLabel('Inv No.')->setValidations('');
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')]);
        $inputController->ajax_dropdown('dc_id')->setLabel('Distributor Customer')->setLink('distributor_customer');
        $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{dis_id}']);
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['so_number', 'inv_number'], ['dis_id', 'dsr_id', 'dc_id'], ['s_date', 'e_date']]);
    }

    function print(Request $request) {
        $value = $request->input('value');

        $contents = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/logo.jpg'));
        $watermark = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/watermark.png'));

        $invoice = DistributorInvoice::with(['lines', 'lines.batch', 'lines.product', 'bonusLines', 'bonusLines.product', 'bonusLines.batch', 'distributor', 'customer'])->find($value);

        $printed = $invoice->di_printed_at;

        $invoiceLines = DistributorInvoiceLine::with(['batch', 'product'])->where('dil_qty', '>', 0)->where('di_id', $invoice->getKey())->withTrashed()->get();

        if (!$printed) {
            $invoice->di_printed_at = date('Y-m-d H:i:s');
            $invoice->save();
        }

        $dis_customer = DistributorCustomer::where('dc_id', $invoice->customer->dc_id)->first();
        $pricesNo = SalesPriceLists::where('spl_id', $dis_customer->price_group)->first();

        $lines = $invoiceLines->map(function (DistributorInvoiceLine $line) use ($pricesNo, $invoice) {

            // $prices = SalesPriceLists::where('sales_price_group_id', $pricesNo ? $pricesNo->sales_price_group_id : 0)
            $prices = SalesPriceLists::where('price_list_no', $pricesNo ? $pricesNo->price_list_no : 0)
                ->where('product_id', $line->product->product_id)
                ->whereDate('valid_from_date', '<=', date('Y-m-d', strtotime($invoice->created_at)))
                ->where('state', '=', 'Active')
                ->latest()
                ->first();

            $batch = $line->batch;

            return [
                'product_name' => $line->product ? $line->product->product_name : "DELETED",
                'pro_code' => $line->product ? $line->product->product_code . ' / ' : "",
                'batch_code' => isset($batch) ? $batch->db_code . ' / ' : "",
                'batch_exp' => isset($batch) ? $batch->db_expire : "",
                'base_price' => isset($prices->base_price) ? number_format($prices->base_price, 2) : 0,
                'whole_price' => $line->dil_unit_price,
                'pack_size' => $line->product ? $line->product->pack_size : "",
                'qty' => $line->dil_qty,
                'bonus' => 0,
                'sale_price' => $line->dil_unit_price,
                'discount' => isset($line->dil_discount_percent) ? $line->dil_discount_percent : 0,
                'amount' => number_format($line->dil_qty * $line->dil_unit_price, 2),
                'raw_amount' => $line->dil_qty * $line->dil_unit_price,
            ];
        });

        $bounsLines = $invoice->bonusLines->map(function (DistributorInvoiceBonusLine $line) {
            $batch = $line->batch;

            return [
                'product_name' => $line->product ? $line->product->product_name : "DELETED",
                'pro_code' => $line->product ? $line->product->product_code . ' / ' : "",
                'batch_code' => isset($batch) ? $batch->db_code . ' / ' : "",
                'batch_exp' => isset($batch) ? $batch->db_expire : "",
                'base_price' => 0,
                'whole_price' => 0,
                'pack_size' => isset($line->product) ? $line->product->pack_size : "",
                'qty' => 0,
                'bonus' => isset($line) ? $line->dibl_qty : 0,
                'sale_price' => 0,
                'discount' => 0,
                'amount' => 0,
                'raw_amount' => 0,
            ];
        });

        $resultlines = $lines->merge($bounsLines);
        $resultlines = $resultlines->SortByDesc('product_name')->values();

        /** @var User $user */
        $user = Auth::user();

        $data = [
            'logo' => $contents,
            'watermark' => $watermark,
            'original' => !!!$printed,
            'dis_name' => $invoice->distributor ? $invoice->distributor->name : "DELETED",
            'customer_code' => $invoice->customer ? $invoice->customer->dc_code : "DELETED",
            'invoice_number' => $invoice->di_number,
            'customer_name' => $invoice->customer ? $invoice->customer->dc_name : "DELETED",
            'invoice_date' => $invoice->created_at->format('Y-m-d'),
            'address' => $invoice->customer ? $invoice->customer->dc_address : "DELETED",
            'wh_code' => "N\\A",
            'printed_user' => $user ? $user->u_code : "SYSTEM",
            'page_count' => ceil($invoice->lines->count() / 31),
            'gross_value' => number_format($resultlines->sum('raw_amount'), 2),
            'discount' => $invoice->di_discount,
            'net_value' => number_format($resultlines->sum('raw_amount') - $invoice->di_discount, 2),
            'lines' => $resultlines,
        ];

        $customPaper = array(0, 0, 609.00, 788.00);
        $pdf = PDF::loadView('invoice-pdf', $data);
        $pdf->setPaper($customPaper, 'potrait');

        $userId = $user ? $user->getKey() : 0;
        $time = time();

        $content = $pdf->download()->getOriginalContent();

        Storage::put('public/pdf/' . $userId . '/' . $time . '.pdf', $content);

        return response()->json([
            'link' => url('/storage/pdf/' . $userId . '/' . $time . '.pdf'),
            'success' => true,
        ]);
    }
}
