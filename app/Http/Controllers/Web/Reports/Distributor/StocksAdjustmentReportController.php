<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Exceptions\WebAPIException;
use App\Models\StockAdjusment;
use App\Models\StockAdjusmentProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PDF;


class StocksAdjustmentReportController extends ReportController
{

    protected $title = "Stock Adjustment Report";

    public function search($request)
    {

        $values = $request->input('values', []);

        $query = DB::table('stock_adjusment as sa')
            ->select([
                'sa.stk_adj_date as date',
                'sa.stk_adj_id',
                'sa.stk_adj_no',
                'sa.dis_id',
                'sap.reason',
                'p.product_code',
                'p.product_name',
                'sa.stk_adj_date',
                'pr.principal_name',
                'db.db_code',
                'db.db_expire',
                'db.db_price',
                'sap.stk_adj_qty'
            ])
            ->join('stock_adjusment_product as sap', 'sap.stk_adj_id', 'sa.stk_adj_id')
            ->join('product as p', 'p.product_id', 'sap.product_id')
            ->join('principal as pr', 'pr.principal_id', 'p.principal_id')
            ->join('distributor_batches as db', 'db.db_id', 'sap.db_id')
            ->where('sa.stk_adj_date', '>=', $values['s_date'])
            ->where('sa.stk_adj_date', '<=', $values['e_date'])
            ->whereNull('sa.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pr.deleted_at')
            ->whereNull('db.deleted_at')
            ->whereNull('sap.deleted_at')
            ->groupBy('sa.stk_adj_id');

        if (isset($values['distributor'])) {
            $query->where('sa.dis_id', $values['distributor']['value']);
        }
        if (isset($values['pro_id'])) {
            $query->where('sap.product_id', $values['pro_id']['value']);
        }
        if (isset($values['adjustment_code'])) {
            $query->where('sa.stk_adj_no', 'LIKE', "%{$values['adjustment_code']}%");
        }

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $query->where('sa.dis_id', $user->getKey());
        }

        $count = $this->paginateAndCount($query, $request, 'stk_adj_id');

        $results = $query->get();

        $results->transform(function ($value) {

            $lines = StockAdjusmentProduct::with(['stockAdjustment', 'product', 'batch'])->where('stk_adj_id', $value->stk_adj_id)->get();

            return [

                'adjustment_code' => $value->stk_adj_no,
                'date' => $value->stk_adj_date,

                'difference' => [

                    'title' => $value->stk_adj_no,
                    'products' => $lines->map(function (StockAdjusmentProduct $val) use ($value) {

                        return [
                            'pro_name' => $val->product->product_name,
                            'batch_no' => $val->batch->db_code,
                            'batch_price' => $val->batch->db_price,
                            'reason' => $val->reason ? $val->reason : '-',
                            'qty' => $val->stk_adj_qty,
                            'amount' => number_format($val->stk_adj_qty * $val->batch->db_price, 2),
                        ];
                    })

                ],
                // 'print_button' => $value->stk_adj_id

            ];
        });

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {

        $columnController->text('adjustment_code')->setLabel('Stock Adjustment Number');
        $columnController->text('date')->setLabel('Date');
        $columnController->custom("difference")->setLabel("Details")->setComponent('SADetails');
        // $columnController->button('print_button')->setLabel('Print')->setLink('report/adjustment/print');
    }

    public function setInputs($inputController)

    {
        $inputController->text('adjustment_code')->setLabel('Stock Adjustment Number')->setValidations('');
        $inputController->ajax_dropdown('distributor')->setLabel('Distributor Name')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')])->setValidations('');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['adjustment_code', 'distributor'], ['s_date', 'e_date']]);
    }

    // public function print(Request $request)
    // {

    //     $value = $request->input('value');

    //     $contents = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/logo.jpg'));
    //     $watermark = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/watermark.png'));


    //     $user = Auth::user();

    //     // $queryNew = DB::table('stock_adjusment as sa')
    //     //     ->select([
    //     //         'sa.stk_adj_date as date',
    //     //         'sa.stk_adj_id',
    //     //         'sa.stk_adj_no',
    //     //         'sa.dis_id',
    //     //         'p.product_code',
    //     //         'p.product_name',
    //     //         'sa.stk_adj_date',
    //     //         'pr.principal_name',
    //     //         'db.db_code',
    //     //         'db.db_expire',
    //     //         'db.db_price',
    //     //         'sap.stk_adj_qty'
    //     //     ])
    //     //     ->join('stock_adjusment_product as sap', 'sap.stk_adj_id', 'sa.stk_adj_id')
    //     //     ->join('product as p', 'p.product_id', 'sap.product_id')
    //     //     ->join('principal as pr', 'pr.principal_id', 'p.principal_id')
    //     //     ->join('distributor_batches as db', 'db.db_id', 'sap.db_id')
    //     //     ->where('sa.stk_adj_date', '>=', $values['s_date'])
    //     //     ->where('sa.stk_adj_date', '<=', $values['e_date'])
    //     //     ->whereNull('sa.deleted_at')
    //     //     ->whereNull('p.deleted_at')
    //     //     ->whereNull('pr.deleted_at')
    //     //     ->whereNull('db.deleted_at')
    //     //     ->whereNull('sap.deleted_at')
    //     //     ->groupBy('sa.stk_adj_id');

    //     $returns = StockAdjusmentProduct::with(['stockAdjustment', 'product', 'batch']);

    //     $lines = $returns->transform(function (StockAdjusmentProduct $value) {
    //         return [
    //             'pro_name' => $value->product->product_name,
    //             'batch_no' => $value->batch->db_code,
    //             'batch_price' => $value->batch->db_price,
    //             'reason' => $value->reason,
    //             'qty' => $value->stk_adj_qty,
    //             'amount' => number_format($value->stk_adj_qty * $value->batch->db_price, 2),
    //         ];
    //     });

    //     $pdf = PDF::loadView(
    //         'stockAdjestmentReport-pdf',
    //         [
    //             'logo' => $contents,
    //             'watermark' => $watermark,
    //             'adjustment_code' => $returns->stockAdjustment->stk_adj_no,
    //             'date' => $returns->stockAdjustment->stk_adj_date,
    //             'printed_user' => $user ? $user->u_code : "NOT LOGGED IN",
    //             'page_count' => ceil($returns->lines->count() / 39),
    //             'items' => $lines,
    //         ]

    //     );

    //     $userId = $user ? $user->getKey() : 0;
    //     $time = time();

    //     $content = $pdf->download()->getOriginalContent();

    //     Storage::put('public/pdf/' . $userId . '/' . $time . '.pdf', $content);

    //     return response()->json([
    //         'link' => url('/storage/pdf/' . $userId . '/' . $time . '.pdf'),
    //         'success' => true,
    //     ]);
    // }
}
