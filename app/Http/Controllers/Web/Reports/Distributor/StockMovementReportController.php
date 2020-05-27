<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\DistributorInvoiceLine;
use App\Models\DistributorStock;
use App\Models\GoodReceivedNoteLine;
use App\Models\Product;
use App\Models\StockAdjusmentProduct;
use App\Models\StockWriteOffProduct;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Query\Builder;

class StockMovementReportController extends ReportController
{

    protected $title = 'Stock Movement Report';

    public function search(\Illuminate\Http\Request $request)
    {

        $validation = Validator::make($request->all(), [
            'values.dis_id' => 'required|array',
            'values.product_id' => 'required|array',
            'values.s_date' => 'required',
            'values.e_date' => 'required'
        ]);

        if ($validation->fails()) {
            throw new WebAPIException("Please fill all fields!");
        }

        $batchId = $request->input('values.batch_id.value');
        $disId = $request->input('values.dis_id.value');
        // $productId = $request->input('values.product_id.value');
        $productId = 2716;
        $startDate = $request->input('values.s_date');
        $endDate = $request->input('values.e_date');

        /** @var Product $product */
        $product = Product::find($request->input('values.product_id.value'));

        if ($batchId) {
            $openingStock = DistributorStock::checkStock($disId . $productId, $batchId, $startDate);
        } else {
            $openingStock = DistributorStock::checkStock($disId, $productId, null, $startDate);
        }

        /** @var Builder $query */
        $query = DistributorStock::with('batch')
            ->where('dis_id', $disId)
            ->where('product_id', $productId)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($batchId)
            $query->where('db_id', $batchId);

        $query->orderBy('created_at', 'asc');
        $query->orderBy('ds_ref_type', 'asc');
        $query->orderBy('ds_ref_id', 'asc');

        $count = $this->paginateAndCount($query, $request, 'ds_ref_id');


        $stocks = $query->get();

        $results = [];
        $lastRefResults = collect([]);

        $lastRefType = 0;
        $lastRefCode = '';
        $lastStock = null;

        $results[] = [
            'date' => $product->product_code,
            'desc' => $product->product_name,
            'ref_1' => 'Opening bal.',
            'ref_2' => '',
            'ref_move' => '',
            'qty' => $openingStock,
            'cur_qty' => $openingStock,
            'batch_no' => '',
            'exp_date' => '',
            'special' => true,
        ];

        $stocks->push(null);

        foreach ($stocks as $key => $stock) {
            /** @var DistributorStock $stock */

            $ref = $stock ? $stock->getRefParent() : null;

            if (!$ref || (($lastRefCode != $ref->getCode() || $lastRefType != $stock->getRefType()) && $key != 0)) {

                $results[] = [
                    'date' => isset($lastStock->created_at) ? $lastStock->created_at->format('Y-m-d') : NULL,
                    'desc' => isset($lastStock) ? $lastStock->getRefType() : NULL,
                    'ref_1' => $lastRefCode,
                    'ref_2' => '',
                    'ref_move' => '',
                    'qty' => $lastRefResults->sum('desc'),
                    'cur_qty' => $openingStock + $lastRefResults->sum('desc'),
                    'batch_no' => isset($stock->batch) ? $stock->batch->db_code : "",
                    'exp_date' => isset($stock->batch) ? $stock->batch->db_expire : "",
                    'special' => true
                ];

                $openingStock += $lastRefResults->sum('desc');

                $results[] = [
                    'date' => 'Batch No',
                    'desc' => 'Qty',
                    'ref_1' => '',
                    'ref_2' => '',
                    'ref_move' => '',
                    'qty' => '',
                    'cur_qty' => '',
                ];

                $results = array_merge($results, $lastRefResults->toArray());

                $results[] = [
                    'date' => 'Total',
                    'desc' => $lastRefResults->sum('desc'),
                    'ref_1' => '',
                    'ref_2' => '',
                    'ref_move' => '',
                    'qty' => '',
                    'cur_qty' => '',
                ];


                $lastRefResults = collect([]);
            }

            $stock = $stock ? $stock : $lastStock;

            $total = 0;
            if (isset($stock->ds_credit_qty) && isset($stock->ds_debit_qty)) {
                $total = $stock->ds_credit_qty - $stock->ds_debit_qty;
            }

            $lastRefResults->push([
                'date' => (isset($stock->batch) ? $stock->batch->db_code : "DELETED") . ' ' . (in_array(isset($stock->ds_ref_type) ? $stock->ds_ref_type : "", [6, 8]) ? "(BONUS)" : ""),
                'desc' => $total,
                'ref_1' => '',
                'ref_2' => '',
                'ref_move' => '',
                'qty' => '',
                'cur_qty' => ''
            ]);

            $lastRefCode = $ref ? $ref->getCode() : "";
            $lastRefType = isset($stock) ? $stock->getRefType() : "";
            $lastStock = $stock;
        }

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    protected function setColumns(\App\Form\Columns\ColumnController $columnController, \Illuminate\Http\Request $request)
    {
        $columnController->date('date')->setLabel('Date')->setSearchable(false);
        $columnController->text('desc')->setLabel('Transaction Desc.')->setSearchable(false);
        $columnController->text('ref_1')->setLabel("Ref. 1")->setSearchable(false);
        $columnController->text('ref_2')->setLabel("Ref. 2")->setSearchable(false);
        $columnController->text('ref_move')->setLabel("Movement Ref.")->setSearchable(false);
        $columnController->text('qty')->setLabel("Trn.Qty")->setSearchable(false);
        $columnController->text('cur_qty')->setLabel('Bal.qty')->setSearchable(false);
        $columnController->text('batch_no')->setLabel('Batch No')->setSearchable(false);
        $columnController->text('exp_date')->setLabel('Expire Date')->setSearchable(false);
    }

    protected function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')]);
        $inputController->ajax_dropdown('product_id')->setLabel('Product')->setLink('product');
        $inputController->ajax_dropdown('batch_id')->setLabel("Batch")->setLink('distributor_batch')->setWhere(['product' => '{product_id}', 'distributor' => '{dis_id}'])->setValidations('');
        $inputController->date('s_date')->setLabel('To');
        $inputController->date('e_date')->setLabel('From');

        $inputController->setStructure([['dis_id', 'product_id', 'batch_id'], ['s_date', 'e_date']]);
    }
}
