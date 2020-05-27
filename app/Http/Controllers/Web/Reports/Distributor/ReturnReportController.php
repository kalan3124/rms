<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Models\User;
use App\Models\DistributorInvoice;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Invoice;
use App\Models\SfaDailyTarget;
use App\Models\SfaSalesOrder;
use DateInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\WebAPIException;
use App\Models\DistributorBatch;
use App\Models\DistributorReturn;
use App\Models\Reason;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PDF;


class ReturnReportController extends ReportController
{

    protected $title = "Return Report";

    protected $updateColumnsOnSearch = true;

    public function search(Request $request)
    {

        $values = $request->input('values');

        if (!isset($values['s_date'])) {
            throw new WebAPIException('start date field is required');
        }

        $query = DB::table('distributor_return_item AS rl')
            ->join('distributor_return AS r', 'r.dis_return_id', 'rl.dis_return_id')
            ->leftjoin('distributor_invoice AS di','di.di_id','r.di_id')
            ->join('distributor_customer AS dc', 'dc.dc_id', 'r.dc_id')
            ->join('users AS dis', 'r.dis_id', 'dis.id')
            ->join('users AS rep', 'rep.id', 'r.dsr_id')
            ->join('product AS p', 'p.product_id', 'rl.product_id')
            ->join('principal As pr' ,'pr.principal_id', 'p.principal_id')
            ->leftjoin('reason AS rsn', 'rl.rsn_id', 'rsn.rsn_id')
            ->select([
                'rl.dis_return_id',
                'rl.created_at',
                'rl.dri_qty',
                'rl.dri_bns_qty',
                'r.deleted_at',
                'r.dist_return_number',
                'di.di_number',
                'rl.dri_price',
                'pr.principal_name',
                'rsn.rsn_name',
                'p.product_name',
                'p.product_code',
                'dis.name AS dis_name',
                'dis.u_code AS dis_code',
                'rep.name AS rep_name',
                'rep.u_code AS rep_code',
                'dc.dc_name',
                'dc.dc_code',
            ]);


        if (isset($values['s_date']) && isset($values['e_date'])) {
            // $query->whereDate('rl.created_at', ">=", date(
            //     "Y-m-d",
            //     strtotime($values['s_date'])
            // ));
            // $query->whereDate('rl.created_at', "<=", date(
            //     "Y-m-d",
            //     strtotime($values['e_date'])
            // ));
            $query->whereBetween( DB::raw( 'DATE(rl.created_at)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        if (isset($values['customer'])) {

            $query->where('dc.dc_id', $values['customer']['value']);
        }

        if (isset($values['invoice_code'])) {

            $query->where('di.di_number','LIKE','%'.$values['invoice_code'].'%');
        }

        if (isset($values['distributor'])) {

            $query->where('r.dis_id', $values['distributor']['value']);
        }

        if (isset($values['product'])) {

            $query->where('p.product_id', $values['product']['value']);
        }

        if (isset($values['salesref'])) {

            $query->where('r.dsr_id', $values['salesref']['value']);
        };

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $query->where('r.dis_id', $user->getKey());
        }


        $grandtot = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('(dri_bns_qty + dri_qty)*dri_price'));

        $count = $this->paginateAndCount($query, $request, 'rl.created_at');

        $results = $query->get();

        $formatedResults = [];

        $lastNumber = "";

        foreach ($results as $key => $userss) {
            $rowNew = [];
            $counts = $results->where('dis_return_id', $userss->dis_return_id)->count();

            // if ($lastNumber != $userss->dist_return_number) {
            //     $dist_code = $userss->dis_return_id;
            //     $rowNew['return_code'] = $userss->dist_return_number;
            //     $rowNew['return_code_rowspan'] = $counts;
            //     $rowNew['invoice_code'] = $userss->di_number;
            //     $rowNew['invoice_code_rowspan'] = $counts;
            //     $rowNew['print_button'] = $userss->dis_return_id;
            //     $rowNew['print_button_rowspan'] = $counts;

            // } else {

            //     $rowNew['return_code'] = null;
            //     $rowNew['return_code_rowspan'] = 0;
            //     $rowNew['invoice_code'] = null;
            //     $rowNew['invoice_code_rowspan'] = 0;
            //     $rowNew['print_button'] = $userss->dis_return_id;
            //     $rowNew['print_button_rowspan'] = 0;
            // }

            $rowNew['s_date'] = $userss->created_at;
            $rowNew['customer_code'] = $userss->dc_code;
            $rowNew['customer'] = $userss->dc_name;
            $rowNew['return_code'] = $userss->dist_return_number;
            $rowNew['invoice_code'] = $userss->di_number;
            $rowNew['principal'] = $userss->principal_name;
            $rowNew['product_code'] = $userss->product_code;
            $rowNew['product'] = $userss->product_name;
            $rowNew['rsn_name'] = $userss->rsn_name;
            $rowNew['sr_code'] = $userss->rep_code;
            $rowNew['salesref'] = $userss->rep_name;
            $rowNew['qty'] = $userss->dri_qty;
            $rowNew['bns_qty'] = $userss->dri_bns_qty;
            $rowNew['net_qty'] = $userss->dri_qty + $userss->dri_bns_qty;
            $rowNew['rtn_value'] = number_format(($userss->dri_qty + $userss->dri_bns_qty) * $userss->dri_price, 2);
            $rowNew['rtn_value_new'] = ($userss->dri_qty + $userss->dri_bns_qty) * $userss->dri_price;
            // $rowNew['print_button'] = $userss->dis_return_id;

            $lastNumber = $userss->dist_return_number;
            $formatedResults[]=$rowNew;
        }

         $results = collect($formatedResults);

         $formatedResults = [
            'special' => true,
            'customer_code' => 'Total',
            'customer' => NULL,
            'return_code' => NULL,
            'invoice_code' => NULL,
            'principal' => NULL,
            'product_code' => NULL,
            'product' => NULL,
            'rsn_name' => NULL,
            'sr_code' => NULL,
            'salesref' => NULL,
            'qty' => $results->sum('qty'),
            'bns_qty' => $results->sum('bns_qty'),
            'net_qty' => $results->sum('net_qty'),
            'rtn_value' => number_format($results->sum('rtn_value_new'), 2),
            's_date' => NULL

        ];

        $rownew = [
            'special' => true,
            'customer_code' => 'Grand Total',
            'customer' => NULL,
            'return_code' => NULL,
            'invoice_code' => NULL,
            'principal' => NULL,
            'product_code' => NULL,
            'product' => NULL,
            'rsn_name' => NULL,
            'sr_code' => NULL,
            'salesref' => NULL,
            'qty' => NULL,
            'bns_qty' => NULL,
            'net_qty' => NULL,
            'rtn_value' => number_format($grandtot, 2),
            's_date' => NULL

        ];

        $results->push($formatedResults);
        $results->push($rownew);

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function print(Request $request)
    {
        $value = $request->input('value');

        $contents = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/logo.jpg'));
        $watermark = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/watermark.png'));


        $returns = DistributorReturn::with('lines','invoice','lines.product', 'distributorRep', 'distributorCustomer', 'distributor', 'bonusLines', 'bonusLines.product','lines.batch','bonusLines.batch')->find($value);

        $lines = $returns->lines->transform(function ($line) {

            if (isset($line->rsn_id))
                $reason = Reason::where('rsn_type', 8)->where('rsn_id', $line->rsn_id)->first();

            return [
                'product_name' => $line->product ? $line->product->product_name : "DELETED",
                'pro_code' => $line->product ? $line->product->product_code . ' / ' : "",
                'batch_code' => isset($line->batch->db_code) ? $line->batch->db_code . ' / ' : "",
                'batch_exp' => isset($line->batch->db_expire) ? $line->batch->db_expire : "",
                'reason' => isset($reason->rsn_name) ? $reason->rsn_name : "",
                'pack_size' => $line->product ? $line->product->pack_size : "",
                'qty' => $line->dri_qty,
                'bonus' => 0,
                'sale_price' => $line->dri_price,
                'discount' => 0,
                'amount' =>  number_format($line->dri_qty * $line->dri_price, 2),
                'sum_amount' =>  $line->dri_qty * $line->dri_price,
            ];
        });

        $bounsLines = $returns->bonusLines->transform(function ($line) {

            return [
                'product_name' => $line->product ? $line->product->product_name : "DELETED",
                'pro_code' => $line->product ? $line->product->product_code . ' / ' : "",
                'batch_code' => isset($line->batch->db_code) ? $line->batch->db_code . ' / ' : "",
                'batch_exp' => isset($line->batch->db_expire) ? $line->batch->db_expire : "",
                'reason' => "",
                'pack_size' => $line->product ? $line->product->pack_size : "",
                'qty' => 0,
                'bonus' => isset($line->drbi_qty) ? $line->drbi_qty : 0,
                'sale_price' => 0,
                'discount' => 0,
                'amount' =>  0,
            ];
        });

        $resultlines = $lines->concat($bounsLines);
        $resultlines = $resultlines->SortByDesc('product_name')->values();

        // return $resultlines;
        /** @var User $user */
        $user = Auth::user();

        $data = [
            'logo' => $contents,
            'watermark' => $watermark,
            'dis_name' => $returns->distributor ? $returns->distributor->name : "DELETED",
            'customer_code' => $returns->distributorCustomer ? $returns->distributorCustomer->dc_code : "DELETED",
            'return_number' => $returns->dist_return_number,

            'invoice_code' => $returns->di_number,

            'customer_name' => $returns->distributorCustomer ? $returns->distributorCustomer->dc_name : "DELETED",
            'address' => $returns->distributorCustomer ? $returns->distributorCustomer->dc_address : "DELETED",
            'return_date' => date('Y-m-d', strtotime($returns->return_date)),
            'rep' => $returns->distributorRep ? $returns->distributorRep->name : "DELETED",
            'gross_value' => number_format($lines->sum('sum_amount'), 2),
            'discount' => $returns->discount,
            'net_value' => number_format($lines->sum('sum_amount') - $returns->discount, 2),
            'lines' => $resultlines,
            'order_return_no' => isset($returns->invoice->di_number)?$returns->invoice->di_number:'',
            'order_return_datey' => isset($returns->invoice->created_at)?date('Y-m-d',strtotime($returns->invoice->created_at)):''
        ];

        $pdf = PDF::loadView('return-pdf', $data);
        $pdf->setPaper('letter', 'potrait');

        $userId = $user ? $user->getKey() : 0;
        $time = time();

        $content = $pdf->download()->getOriginalContent();

        Storage::put('public/pdf/' . $userId . '/' . $time . '.pdf', $content);

        return response()->json([
            'link' => url('/storage/pdf/' . $userId . '/' . $time . '.pdf'),
            'success' => true,
        ]);
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {


        $columnController->text('customer_code')->setLabel('Customer Code');
        $columnController->text('customer')->setLabel('Customer Name');
        $columnController->text('return_code')->setLabel('Return Report Number');
        $columnController->text('invoice_code')->setLabel('Invoice Number');
        $columnController->text('principal')->setLabel('Agency Name');
        $columnController->text('product_code')->setLabel('Product Code');
        $columnController->text('product')->setLabel('Product Name');
        $columnController->text('rsn_name')->setLabel('Return Reason');
        $columnController->text('sr_code')->setLabel('SR Code');
        $columnController->text('salesref')->setLabel('SR Name');
        $columnController->number('qty')->setLabel('Qty');
        $columnController->number('bns_qty')->setLabel('Bonus');
        $columnController->number('net_qty')->setLabel('Net Return Qty');
        $columnController->number('rtn_value')->setLabel('Return Goods Values');
        $columnController->text('s_date')->setLabel('Date');
        // $columnController->button('print_button')->setLabel('Print')->setLink('report/return/print');
    }

    public function setInputs($inputController)

    {
        // $inputController->text('gqw')->setLabel('Text')->setValidations('');
        $inputController->ajax_dropdown('customer')->setLabel('Customer Name')->setLink('distributor_customer')->setValidations('');
        $inputController->ajax_dropdown('distributor')->setLabel('Distributor Name')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')])->setValidations('');
        $inputController->ajax_dropdown('product')->setLabel('Product Name')->setLink('product')->setValidations('');
        $inputController->text('invoice_code')->setLabel('Invoice Number')->setValidations('');
        $inputController->ajax_dropdown('salesref')->setLabel('SR Name')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type'), 'dis_id' => '{dis_id}'])->setValidations('');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['customer', 'distributor'], ['product', 'salesref','invoice_code'], [ 's_date', 'e_date']]);
    }
}
