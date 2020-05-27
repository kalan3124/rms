<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\DistributorInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\DistributorPayment;
use App\Models\DistributorPaymentInvoice;
use Illuminate\Support\Facades\Auth;

class InvoicePaymentController extends Controller {
    public function load(Request $request){
        // return $request->distributor['value'];
        $query = [];

        if(isset($request->number)){
            $query['di_number'] = $request->input('number');
        }

        if(isset($request->distributor['value'])){
            $query['dis_id'] = $request->distributor['value'];
        }

        if(isset($request->salesman['value'])){
            $query['dsr_id'] = $request->salesman['value'];
        }

        if(isset($request->customer['value'])){
            $query['dc_id'] = $request->customer['value'];
        }

        // return $query;


        // return $number;

        // if(empty(trim($number)))
        //     throw new WebAPIException("Please fill the Invoice number field.");

        /** @var DistributorInvoice $invoice */
        if(count($query)>0){
            $invoice = DistributorInvoice::with(['distributor','customer','distributorSalesRep'])->where($query)->where('payment_status',0)->get();
        }else{
            throw new WebAPIException("Select Invoice number or Customer to Search.");
        }

        if(!$invoice || $invoice->count()==0){
            throw new WebAPIException("Can not find a invoice for the given number.");
        }

        // $cos_amount = $invoice->sum('di_amount')-$invoice->sum('di_discount');
        // return $invoice->first()->customer->dc_name;

        $cos_amount = 0;
        foreach ($invoice as $key => $value) {
            if($value->balance>0){
                $cos_amount += $value->balance;
            }else{
                $cos_amount += ($value->di_amount - $value->di_discount);
            }
        }


        // if($invoice){
        //     throw new WebAPIException("This invoice is already paid.");
        // }

        // $disId = $purchaseOrder->dis_id;

        $customer = [
            'label'=>$invoice->first()->customer->dc_name,
            'value'=>$invoice->first()->customer->dc_id
        ];



        $lines = $invoice->transform(function($invoice,$key){
            return [
                'id'=>$key,
                'in_id'=>$invoice->di_id,
                'date'=>$invoice->created_at->format('Y-m-d'),
                'code'=>$invoice->di_number,
                'in_amount'=>($invoice->di_amount - $invoice->di_discount),
                'os_amount'=>$invoice->balance>0?$invoice->balance:($invoice->di_amount - $invoice->di_discount),
                'payment_amount' => 0.00,
                'balance_amount' => 0.00,
                'status'=>false
            ];
        });

        return response()->json([
            'success'=>true,
            'customer'=>$customer,
            'cos_amount'=>$cos_amount,
            'lines'=>$lines
        ]);
    }

    public function save(Request $request){
        // return $request->customer['value'];
        $validation = Validator::make($request->all(),[
            'lines'=>'required|array',
            'payment'=>'required',
            'customer'=>'required|array',
            'balance'=>'required',
            'pType'=>'required|array',
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please fill all inputs.");
        }

        $payment = $request->payment;
        $balance = $request->balance;
        $lines = $request->input('lines');
        $customer = $request->customer['value'];
        $pType = $request->pType['value'];

        $c_no=$request->c_no;
        $c_bank=$request->c_bank;
        $c_branch=$request->c_branch;
        $c_date=$request->c_date;

        if($pType==2){
            if(!$c_no){
                throw new WebAPIException("CHEQUE NO FIELD IS REQUIRED");
            }
            if(!$c_bank){
                throw new WebAPIException("BANK FIELD IS REQUIRED");
            }
            if(!$c_branch){
                throw new WebAPIException("BANK BRANCH FIELD IS REQUIRED");
            }
            if(!$c_date){
                throw new WebAPIException("BANKING DATE IS REQUIRED");
            }
        }


        try {

            DB::beginTransaction();

            $dis_payment_id = DistributorPayment::create([
                'dc_id'=>$customer,
                'amount'=>$payment,
                'balance'=>$balance,
                'date'=>today(),
                'p_code'=>DistributorPayment::generateNumber(),
                'u_id'=>Auth::user()->id,
                'payment_type_id'=>$pType,
                'c_no'=>$c_no,
                'c_bank'=>$c_bank,
                'c_branch'=>$c_branch,
                'c_date'=>$c_date,
            ]);
            // return $dis_payment_id;

            // dump($dis_payment);
            foreach ($lines as $key => $line) {
                if($line['status']){
                    if($line['in_id'] != null){
                        $dis_payment = DistributorPaymentInvoice::create([
                            'distributor_payment_id'=>$dis_payment_id->id,
                            'di_id'=>$line['in_id'],
                            'amount'=>$line['payment_amount']
                        ]);

                        if($line['balance_amount']==0){
                            $dis_invoice = DistributorInvoice::where('di_id',$line['in_id'])->first();
                            $dis_invoice->balance = $line['balance_amount'];
                            $dis_invoice->payment_status = 1;
                            $dis_invoice->update();
                        }else{
                            $dis_invoice = DistributorInvoice::where('di_id',$line['in_id'])->first();
                            $dis_invoice->balance = $line['balance_amount'];
                            $dis_invoice->update();
                        }
                    }
                }

            }

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            // return $e;
            throw new WebAPIException("Server error appeared. Please contact your system vendor.");
        }



        return response()->json([
            'success'=>true,
            'message'=>'Payment Successfully'
        ]);
    }
}
