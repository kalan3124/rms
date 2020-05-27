<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Exceptions\WebAPIException;
use App\Models\StockAdjusment;
use App\Models\StockAdjusmentProduct;
use App\Models\StockWriteOff;
use App\Models\StockWriteOffProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PDF;

class StockWriteOffReportController extends ReportController
{

    protected $title = "Stock Write Off Report";

    public function search($request)
    {

        $values = $request->input('values', []);

        $query = DB::table('write_off as wo')
            ->select([
                'wo.wo_date as date',
                'wo.wo_no',
                'wop.wo_id',
                'wo.dis_id',
                'wop.reason',
                'p.product_code',
                'p.product_name',
                'pr.principal_name',
                'db.db_code',
                'db.db_expire',
                'db.db_price',
                'wop.wo_qty'
            ])
            ->join('write_off_product as wop', 'wop.wo_id', 'wo.wo_id')
            ->join('product as p', 'p.product_id', 'wop.product_id')
            ->join('principal as pr', 'pr.principal_id', 'p.principal_id')
            ->join('distributor_batches as db', 'db.db_id', 'wop.db_id')
            ->where('wo.wo_date', '>=', $values['s_date'])
            ->where('wo.wo_date', '<=', $values['e_date'])
            ->whereNull('wo.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pr.deleted_at')
            ->whereNull('db.deleted_at')
            ->whereNull('wop.deleted_at')
            ->groupBy('wo.wo_id');

        if (isset($values['distributor'])) {
            $query->where('wo.dis_id', $values['distributor']['value']);
        }

        if (isset($values['pro_id'])) {
            $query->where('wop.product_id', $values['pro_id']['value']);
        }

        if (isset($values['wo_code'])) {

            $query->where('wo.wo_no', 'LIKE', "%{$values['wo_code']}%");
        }

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $query->where('wo.dis_id', $user->getKey());
        }

        $count = $this->paginateAndCount($query, $request, 'wo_id');

        $results = $query->get();

        // $lines=0;
        $results->transform(function ($value) {

            $lines = StockWriteOffProduct::with(['product', 'writeoff','batch'])->where('wo_id', $value->wo_id)->get();

            return [

                'wo_code' => $value->wo_no,
                'date' => $value->date,
                'difference' => [

                    'title' => $value->wo_no,
                    'products' => $lines->map(function (StockWriteOffProduct $val) use ($value) {

                        return [
                            'pro_name' => $val->product->product_name,
                            'batch_no' => $val->batch->db_code,
                            'batch_price' => $val->batch->db_price,
                            'reason' => $val->reason?$val->reason:'-',
                            'qty' => $val->wo_qty,
                            'amount' => number_format($val->wo_qty * $val->batch->db_price, 2),

                        ];
                    })

                ],
                // 'print_button' => $value->wo_id

            ];
        });

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {

        $columnController->text('wo_code')->setLabel('Write Off Number');
        $columnController->text('date')->setLabel('Date');
        $columnController->custom("difference")->setLabel("Details")->setComponent('SWODetails');
        // $columnController->button('print_button')->setLabel('Print')->setLink('report/writeoff/print');
    }

    public function setInputs($inputController)

    {
        $inputController->text('wo_code')->setLabel('Write Off Number')->setValidations('');
        $inputController->ajax_dropdown('distributor')->setLabel('Distributor Name')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')])->setValidations('');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['wo_code', 'distributor'], ['s_date', 'e_date']]);
    }

    // public function print(Request $request)
    // {

    //     $value = $request->input('value');

    //     $contents = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/logo.jpg'));
    //     $watermark = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/watermark.png'));


    //     $user = Auth::user();

    //     // $queryNew = DB::table('write_off as wo')
    //     //     ->select([
    //     //         'wo.wo_date as date',
    //     //         'wo.wo_no',
    //     //         'wo.dis_id',
    //     //         'wop.reason',
    //     //         'p.product_code',
    //     //         'p.product_name',
    //     //         'pr.principal_name',
    //     //         'db.db_code',
    //     //         'db.db_expire',
    //     //         'db.db_price',
    //     //         'wop.wo_qty'
    //     //     ])
    //     //     ->join('write_off_product as wop', 'wop.wo_id', 'wo.wo_id')
    //     //     ->join('product as p', 'p.product_id', 'wop.product_id')
    //     //     ->join('principal as pr', 'pr.principal_id', 'p.principal_id')
    //     //     ->join('distributor_batches as db', 'db.db_id', 'wop.db_id')
    //     //     ->where('wo.wo_date', '>=', $values['s_date'])
    //     //     ->where('wo.wo_date', '<=', $values['e_date'])
    //     //     ->whereNull('wo.deleted_at')
    //     //     ->whereNull('p.deleted_at')
    //     //     ->whereNull('pr.deleted_at')
    //     //     ->whereNull('db.deleted_at')
    //     //     ->whereNull('wop.deleted_at')
    //     //     ->groupBy('wo.wo_id');



    //     $returns = StockWriteOffProduct::with(['product', 'writeoff', 'batch'])->get();

    //     $lines = $returns->map(function (StockWriteOffProduct $value) {

    //         return [
    //             'pro_name' => $value->product->product_name,
    //             'batch_no' => $value->batch->db_code,
    //             'batch_price' => $value->batch->db_price,
    //             'reason' => $value->reason,
    //             'qty' => $value->wo_qty,
    //             'amount' => number_format($value->wo_qty * $value->batch->db_price, 2),
    //         ];
    //     });

    //     /** @var User $user */
    //     $user = Auth::user();

    //     $data = $returns->map(function (StockWriteOff $val) {

    //         return [
    //             'logo' => $contents,
    //             'watermark' => $watermark,
    //             'wo_code' => $val->wo_no,
    //             'date' => $val->wo_date,
    //             // 'printed_user' => $user ? $user->u_code : "NOT LOGGED IN",
    //             // 'page_count' => ceil($returns->lines->count() / 39),
    //             // 'items' => $lines,
    //         ];
    //     });




    //     $pdf = PDF::loadView(
    //         'stockAdjestmentReport-pdf',
    //         $data
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
