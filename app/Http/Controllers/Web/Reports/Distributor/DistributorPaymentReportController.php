<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\User;
use App\Models\DistributorPayment;
use App\Models\DistributorPaymentInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDF;

class DistributorPaymentReportController extends ReportController
{
    protected $title = "Payment Report";

    public function search($request)
    {

        $values = $request->input('values');
        // return $values['p_type']['value'];

        $paymentQuery = DB::table('distributor_payments AS i')
        ->join('distributor_customer AS dc', 'i.dc_id', '=', 'dc.dc_id')
        // ->join('payment_types AS pt', 'i.payment_type_id', '=', 'pt.id')
        ->select([
            'i.id',
            'i.amount',
            'i.created_at',
            'i.u_id',
            'dc.dc_id',
            'dc.dc_name',
            'i.p_code',
            // 'pt.name as pt_name',
            'i.payment_type_id'
        ]);

        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'id':
                $sortBy = 'i.id';
                break;
            default:
                $sortBy = 'i.created_at';
                break;
        }

        $dcId = $request->input('values.dc_id');
        $pCode = $request->input('values.p_code');
        $pType = $request->input('values.p_type.value');

        if ($dcId) {
            $paymentQuery->where('dc.dc_id', $dcId['value']);
        }


        if ($pCode) {
            $paymentQuery->where('i.p_code', $pCode);
        }


        if ($pType) {
            $paymentQuery->where('i.payment_type_id', $pType);
        }

        $grandtot = DB::table(DB::raw("({$paymentQuery->toSql()}) as sub"))
            ->mergeBindings(get_class($paymentQuery) == 'Illuminate\Database\Eloquent\Builder' ? $paymentQuery->getQuery() : $paymentQuery)->sum(DB::raw('amount'));

        $count = $this->paginateAndCount($paymentQuery, $request, $sortBy);

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $invoiceQuery->where('dis.id', $user->getKey());
        }

        if (isset($values['s_date']) && isset($values['e_date'])) {
            $paymentQuery->whereBetween(DB::raw('DATE(i.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        $payments = $paymentQuery->get();

        $payments->transform(function ($payment) {

            $color = [];

            if (isset($payment->printed_at)) {
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

            $paymentLines = DistributorPaymentInvoice::where('distributor_payment_id', $payment->id)->with(['invoice'])->get();
            // ->withTrashed()

            $paymentLines->transform(function (DistributorPaymentInvoice $paymentLine) use ($payment) {
                return [
                    'invoice_code'=>$paymentLine->invoice->di_number,
                    'amount'=>number_format($paymentLine->amount,2),
                    // 'created_at'=>$paymentLine->invoice->created_at,
                ];
            });

            return [
                'id_style'=>$color,
                'id'=>$payment->id,
                'p_code_style'=>$color,
                'p_code'=>$payment->p_code,
                'amount_style'=>$color,
                'amount'=>number_format($payment->amount,2),
                'amount_t'=>$payment->amount,
                'pt_name_style'=>$color,
                // 'pt_name'=>$payment->pt_name,
                'pt_name'=>$payment->payment_type_id==1?'Cash':'Cheque',
                'created_at_style'=>$color,
                'created_at'=>$payment->created_at,
                'dc_id_style' => $color,
                'dc_id' => [
                    'value' => $payment->dc_id,
                    'label' => $payment->dc_name,
                ],
                'details_style' => $color,
                'details' => [
                    'title' => $payment->p_code,
                    'invoices' => $paymentLines,
                ],
                'print_button_style' => $color,
                'print_button' => $payment->id,
            ];
        });

        $newRow = [];
        $newRow = [
            'special' => true,
            'p_code'=>'Total',
            'dc_id'=>null,
            'pt_name'=>null,
            'created_at'=>null,
            'amount'=>number_format($payments->sum('amount_t'), 2),
            'details'=>null,
            'print_button' => null,
        ];

        $newRownew = [
            'special' => true,
            'p_code'=>'Grand Total',
            'dc_id'=>null,
            'pt_name'=>null,
            'created_at'=>null,
            'amount'=>number_format($grandtot, 2),
            'details'=>null,
            'print_button' => null,
        ];

        $payments->push($newRow);
        $payments->push($newRownew);

        return [
            'results' => $payments,
            'count' => $count,
        ];
    }

    protected function getAdditionalHeaders($request)
    {
        $columns = [[
            [
                "title" => "Green: Printed Payment | Red: Not Printed Payment",
                "colSpan" => 7,
            ],
        ]];

        return $columns;
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('p_code')->setLabel('Payment Recipt No.');
        $columnController->ajax_dropdown('dc_id')->setLabel('Distributor Customer');
        $columnController->number('pt_name')->setLabel('Payment Method');
        $columnController->text('created_at')->setLabel('Created Date / Time');
        $columnController->number('amount')->setLabel('Amount');
        $columnController->custom("details")->setLabel("Invoices")->setComponent('PaymentDetails');
        $columnController->button('print_button')->setLabel('Print')->setLink('report/payment/print');
    }

    public function setInputs($inputController)
    {
        $inputController->text('p_code')->setLabel('Payment Recipt No.')->setValidations('');
        $inputController->ajax_dropdown('dc_id')->setLabel('Distributor Customer')->setLink('distributor_customer')->setValidations('');
        $inputController->select("p_type")->setLabel("Payment Method")->setOptions([
            1=>"Cash",
            2=>"Cheque",
        ])->setValidations('');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['p_code', 'p_type','dc_id'], ['s_date', 'e_date']]);
    }

    function print(Request $request) {
        $value = $request->input('value');
        $contents = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/logo.jpg'));
        $watermark = base64_encode(file_get_contents(__DIR__ . '/../../../../../../public/images/watermark.png'));

        $payment = DistributorPayment::with(['lines', 'lines.invoice', 'customer'])->find($value);
        $printed = $payment->printed_at;

        $paymentLines = DistributorPaymentInvoice::with(['invoice'])->where('distributor_payment_id',$value )->get();

        if (!$printed) {
            $payment->printed_at = date('Y-m-d H:i:s');
            $payment->save();
        }

        $lines = $paymentLines->map(function (DistributorPaymentInvoice $line) use ($payment) {
            return [
                'invoice_code' => $line->invoice->di_number,
                'amount' => $line->amount,
            ];
        });


        /** @var User $user */
        $user = Auth::user();

        $data = [
            'watermark' => $watermark,
            'recipt_no'=>$payment->p_code,
            'date'=>$payment->created_at->format('Y-m-d'),
            'customer'=>$payment->customer->dc_name,
            'p_type'=>$payment->payment_type_id==1?'Cash':'Cheque',
            'amount'=>$payment->amount,
            'remarks'=>$lines,
            'printed_user' => $user ? $user->u_code : "SYSTEM",
            'original' => !!!$printed,
            'page_count' => ceil($payment->lines->count() / 31),
        ];

        $customPaper = array(0, 0, 609.00, 788.00);
        $pdf = PDF::loadView('payment-pdf', $data);
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
