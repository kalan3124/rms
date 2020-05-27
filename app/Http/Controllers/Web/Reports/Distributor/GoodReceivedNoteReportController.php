<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\GoodReceivedNote;
use App\Models\GoodReceivedNoteLine;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use Dompdf\Dompdf;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class GoodReceivedNoteReportController extends ReportController
{
    protected $title = "Goods Received Note Report";

    public function search(Request $request)
    {

        $values = $request->input('values');

        $grnNumber = $request->input("values.grn_number");
        $poNumber = $request->input('values.po_number');
        $startDate = $request->input('values.s_date');
        $endDate = $request->input('values.e_date');
        $disId = $request->input('values.dis_id.value');
        $dsrId = $request->input('values.sr_id.value');

        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'grn_number':
                $sortBy = 'grn_no';
                break;
            case 'grn_amount':
                $sortBy = 'grn_amount';
                break;
            default:
                $sortBy = 'created_at';
                break;
        }

        $query = GoodReceivedNote::with([
            'distributor',
            'distributorSalesRep',
            'purchaseOrder',
            'purchaseOrder.lines',
            'lines',
            'lines.product',
            'lines.distributorBatch'
        ]);

        if ($grnNumber) {
            $query->where('grn_no', 'LIKE', "%$grnNumber%");
        }

        if ($poNumber) {
            $purchaseOrders = PurchaseOrder::where('po_number', 'LIKE', "%$poNumber%")->get();
            $query->whereIn('po_id', $purchaseOrders->pluck('po_id'));
        }

        // if($startDate&&$endDate){
        //     $query->whereBetween(DB::raw('DATE(grn_date)'),[$startDate,$endDate]);
        // }
        if (isset($values['s_date']) && isset($values['e_date'])) {
            $query->whereBetween(DB::raw('DATE(grn_date)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        if ($disId) {
            $query->where('dis_id', $disId);
        }

        if ($dsrId) {
            $query->where('dsr_id', $dsrId);
        }

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $query->where('dis_id', $user->getKey());
        }


         if (isset($values['status']) && $values['status']['value'] == 1) {
            $query->whereNotNull('grn_confirmed_at');
        }

        else if (isset($values['status']) && $values['status']['value'] == 2) {
            $query->whereNull('grn_confirmed_at');
        }

        $grandtot = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('grn_amount'));

        $count = $this->paginateAndCount($query, $request, $sortBy);

        $results = $query->get();

        $results->transform(function (GoodReceivedNote $grn) {

            $purchaseOrderLines = $grn->purchaseOrder ? $grn->purchaseOrder->lines : collect([]);
            $grnLines = collect($grn->lines);

            $details = [];

            foreach ($purchaseOrderLines as $polKey => $pol) {
                /** @var Collection|GoodReceivedNoteLine[] $matchedGRNLines */
                $matchedGRNLines = $grnLines->where('product_id', $pol->product_id)->values();

                $key = 0;
                foreach ($matchedGRNLines as $grnlKey => $grnl) {

                    $grnAmount = 0;

                    if ($key == 0) {
                        foreach ($matchedGRNLines as  $grnl_2) {
                            $grnAmount += ($grnl_2->distributorBatch ? $grnl_2->distributorBatch->db_price : 0) * $grnl_2->grnl_qty;
                        }
                    }

                    $details[] =  [
                        'product' => $pol->product ? [
                            'value' => $pol->product->product_id,
                            'label' => $pol->product->product_name
                        ] : [
                            'value' => 0,
                            'label' => 'DELETED'
                        ],
                        'po_price' => $pol->pol_price,
                        'po_qty' => $pol->pol_qty,
                        'pol_id' => $pol->getKey(),
                        'po_amount' => number_format($pol->pol_amount, 2),
                        'po_rowspan' => $key == 0 ? $matchedGRNLines->count() : 0,
                        'grn_batch' => $grnl->distributorBatch ? [
                            'value' => $grnl->distributorBatch->db_id,
                            'label' => $grnl->distributorBatch->db_code
                        ] : [
                            'value' => 0,
                            'label' => 'DELETED'
                        ],
                        'grn_expire' => $grnl->distributorBatch->db_expire ? $grnl->distributorBatch->db_expire : '-',
                        'grn_price' => number_format($grnl->distributorBatch ? $grnl->distributorBatch->db_price : 0, 2),
                        'grn_qty' => $grnl->grnl_qty,
                        'grnl_id' => $grnl->getKey(),
                        'grn_amount' => number_format($grnl->grnl_qty * ($grnl->distributorBatch ? $grnl->distributorBatch->db_price : 0), 2),
                        'dif_qty' => $key == 0 ? $pol->pol_qty - $matchedGRNLines->sum('grnl_qty') : 0,
                        'dif_amount' => number_format($key = 0 ? $pol->pol_amount - $grnAmount : 0, 2)
                    ];

                    $key++;
                }
            }

            $poNotExistLines = $grnLines->whereNotIn('product_id', $purchaseOrderLines->pluck('product_id')->all());

            foreach ($poNotExistLines as $key => $poNotExist) {
                /** @var GoodReceivedNoteLine $poNotExist */
                $details[] =  [
                    'product' => $poNotExist->product ? [
                        'value' => $poNotExist->product->product_id,
                        'label' => $poNotExist->product->product_name
                    ] : [
                        'value' => 0,
                        'label' => 'DELETED'
                    ],
                    'po_price' => "N/A",
                    'po_qty' => 0,
                    'pol_id' => 0,
                    'po_amount' => 0,
                    'po_rowspan' => 1,
                    'grn_batch' => $poNotExist->distributorBatch ? [
                        'value' => $poNotExist->distributorBatch->db_id,
                        'label' => $poNotExist->distributorBatch->db_code
                    ] : [
                        'value' => 0,
                        'label' => 'DELETED'
                    ],
                    'grn_expire' => $poNotExist->distributorBatch->db_expire ? $poNotExist->distributorBatch->db_expire : '-',
                    'grn_price' => number_format($poNotExist->distributorBatch ? $poNotExist->distributorBatch->db_price : 0, 2),
                    'grn_qty' => $poNotExist->grnl_qty,
                    'grnl_id' => $poNotExist->getKey(),
                    'grn_amount' => number_format($poNotExist->grnl_qty * ($poNotExist->distributorBatch ? $poNotExist->distributorBatch->db_price : 0), 2),
                    'dif_qty' => $poNotExist->grnl_qty,
                    'dif_amount' => number_format($poNotExist->grnl_qty * ($poNotExist->distributorBatch ? $poNotExist->distributorBatch->db_price : 0), 2)
                ];
            }

            return [
                'po_number' => $grn->purchaseOrder ? $grn->purchaseOrder->po_number : "DELETED",
                'grn_number' => $grn->grn_no,
                'dis_id' => $grn->distributor ? [
                    'value' => $grn->distributor->getKey(),
                    'label' => $grn->distributor->name
                ] : [
                    'value' => 0,
                    'label' => "DELETED"
                ],
                'sr_id' =>  $grn->distributorSalesRep ? [
                    'value' => $grn->distributorSalesRep->getKey(),
                    'label' => $grn->distributorSalesRep->name
                ] : [
                    'value' => 0,
                    'label' => "DELETED"
                ],
                'po_amount_undefined' => $grn->purchaseOrder ? $grn->purchaseOrder->po_amount : "DELETED",
                'po_amount' => number_format($grn->purchaseOrder ? $grn->purchaseOrder->po_amount : "DELETED", 2),
                'grn_amount_undefined' => $grn->grn_amount,
                'grn_amount' => number_format($grn->grn_amount, 2),
                'po_created_at' => $grn->purchaseOrder ? $grn->purchaseOrder->created_at->format('Y-m-d H:i:s') : "DELETED",
                'grn_created_at' => $grn->grn_date,
                'grn_confirm_at' => $grn->grn_confirmed_at? $grn->grn_confirmed_at : 'Pending',
                'difference' => [
                    'products' => $details,
                    'title' => $grn->grn_no
                ],
                'confirmation' => $grn->grn_confirmed_at ? $grn->grn_confirmed_at : [
                    'label' => "Confirmation",
                    'link' => '/distributor/stock/grn_confirm/' . str_replace('/', '_', $grn->grn_no),
                    'react' => true
                ],
                'print_button' => $grn->getKey()
            ];
        });



        $newRow = [];
        $newRow  = [
            'special' => true,
            'po_number' => 'Total',
            'grn_number' => NULL,
            'dis_id' => NULL,
            'sr_id' => NULL,
            'po_amount' => number_format($results->sum('po_amount_undefined'), 2),
            'grn_amount' => number_format($results->sum('grn_amount_undefined'), 2),
            'po_created_at' => NULL,
            'grn_created_at' => NULL,
            'grn_confirm_at' => NULL,
            'difference' => NULL,
            'confirmation' => NULL,
            'print_button' => NULL
        ];

        $newRownew  = [
            'special' => true,
            'po_number' => 'Grand Total',
            'grn_number' => NULL,
            'dis_id' => NULL,
            'sr_id' => NULL,
            'po_amount' => NULL,
            'grn_amount' => number_format($grandtot, 2),
            'po_created_at' => NULL,
            'grn_created_at' => NULL,
            'grn_confirm_at' => NULL,
            'difference' => NULL,
            'confirmation' => NULL,
            'print_button' => NULL
        ];


        $results->push($newRow);
        $results->push($newRownew);

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('po_number')->setLabel('Order No.');
        $columnController->text('grn_number')->setLabel('GRN No.');
        $columnController->ajax_dropdown('dis_id')->setLabel('Distributor');
        $columnController->ajax_dropdown('sr_id')->setLabel('Distributor Sales Rep.');
        $columnController->number('po_amount')->setLabel('PO Amount');
        $columnController->number('grn_amount')->setLabel('GRN Amount');
        $columnController->text('po_created_at')->setLabel('PO Created Time');
        $columnController->text('grn_created_at')->setLabel('GRN Time');
        $columnController->text('grn_confirm_at')->setLabel('GRN Confirm');
        $columnController->custom("difference")->setLabel("Line Wise Difference")->setComponent('GRNDetails');
        $columnController->link("confirmation")->setLabel("GRN Confirmation");
        $columnController->button('print_button')->setLabel('Print')->setLink('report/good_received_note/print');
    }

    public function setInputs($inputController)
    {
        $inputController->text('po_number')->setLabel('Order No.')->setValidations('');
        $inputController->text('grn_number')->setLabel('GRN No.')->setValidations('');
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')]);
        $inputController->ajax_dropdown('sr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type'), 'dis_id' => '{dis_id}']);
        $inputController->select('status')->setLabel("Type")->setOptions([1 => "Confirm", 2 => "Pending"]);
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['grn_number', 'po_number'], ['dis_id', 'sr_id'], ['status', 's_date', 'e_date']]);
    }

    public function print(Request $request)
    {

        $value = $request->input('value');

        $user = Auth::user();

        $grn = GoodReceivedNote::with([
            'distributor',
            'distributorSalesRep',
            'purchaseOrder',
            'purchaseOrder.lines',
            'lines',
            'lines.product',
            'lines.distributorBatch'
        ])->find($value);

        $lines = $grn->lines->map(function (GoodReceivedNoteLine $grnl) {
            return [
                'line_number' => $grnl->grnl_line_no,
                'product_code' => $grnl->product ? $grnl->product->product_code : "DELETED",
                'product_name' => $grnl->product ? $grnl->product->product_name : "DELETED",
                'uom' => $grnl->grnl_uom,
                'batch_code' => $grnl->distributorBatch ? $grnl->distributorBatch->db_code : "DELETED",
                'qty' => $grnl->grnl_qty,
                'loc_num' => $grnl->grnl_loc_no,
                'expire_date' => $grnl->distributorBatch ? $grnl->distributorBatch->db_expire : "BATCH DELETED",
            ];
        });

        $pdf = PDF::loadView('grn-pdf', [
            'po_number' => $grn->purchaseOrder ? $grn->purchaseOrder->po_number : "DELETED",
            'grn_number' => $grn->grn_no,
            'dis_code' => $grn->distributor ? $grn->distributor->u_code : "DELETED",
            'grn_date' => $grn->grn_date,
            'dis_name' => $grn->distributor ? $grn->distributor->name : "DELETED",
            'site_name' => $grn->purchaseOrder && $grn->purchaseOrder->site ? $grn->purchaseOrder->site->site_name : "N/A",
            'printed_user' => $user ? $user->u_code : "NOT LOGGED IN",
            'page_count' => ceil($grn->lines->count() / 39),
            'items' => $lines,
        ]);

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
