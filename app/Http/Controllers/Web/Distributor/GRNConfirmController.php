<?php

namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\DistributorStock;
use App\Models\GoodReceivedNote;
use App\Models\GoodReceivedNoteLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GRNConfirmController extends Controller {
    public function loadInformations(Request $request){

        $validation = Validator::make($request->all(),[
            'grnNumber'=>'required'
        ]);

        if($validation->fails())
            throw new WebAPIException("Please provide a GRN number to search");

        $grnNumber = $request->input('grnNumber');

        /** @var GoodReceivedNote $grn */
        $grn = GoodReceivedNote::with(['lines','lines.distributorBatch','lines.product'])->where('grn_no',trim($grnNumber))->latest()->first();

        if($grn->grn_confirmed_at)
            throw new WebAPIException("This GRN is already confirmed.");

        
        $lines = $grn->lines->transform(function(GoodReceivedNoteLine $grnl){
            return [
                'id'=>$grnl->getKey(),
                'qty'=>$grnl->grnl_qty,
                'orgQty'=>$grnl->grnl_qty,
                'product'=>$grnl->product?[
                    'label'=>$grnl->product->product_name,
                    'value'=>$grnl->product->getKey()
                ]:[
                    'label'=>'DELETED',
                    'value'=>0,
                ],
                'expired'=>$grnl->distributorBatch?$grnl->distributorBatch->db_expire:date('Y-m-d'),
                'batch'=>$grnl->distributorBatch?[
                    'label'=>$grnl->distributorBatch->db_code,
                    'value'=>$grnl->distributorBatch->db_id,
                ]:[
                    'label'=>"DELETED",
                    'value'=>0
                ],
                'price'=>$grnl->grnl_price
            ];
        });

        return response()->json([
            'success'=>true,
            'products'=>$lines,
            'grnId'=>$grn->getKey()
        ]);
    }

    public function save(Request $request){

        $validation = Validator::make($request->all(),[
            'id'=>'required|numeric|exists:good_received_note,grn_id',
            'lines'=>'required|array',
            'lines.*.id'=>'required|numeric|exists:good_received_note_line,grnl_id',
            'lines.*.qty'=>'required|numeric',
        ]);

        if($validation->fails())
            throw new WebAPIException("Invalid request. Please fill all inputs.");

        $grn = GoodReceivedNote::with('lines')->where('grn_id',trim($request->input('id')))->latest()->first();

        $user = Auth::user();

        try{
            DB::beginTransaction();

            if($grn->grn_confirmed_at)
                throw new WebAPIException("Someone confirmed this GRN before you.");

            $lines = $request->input('lines');

            $total = 0;

            foreach($lines as $line){
                /** @var GoodReceivedNoteLine $orgLine */
                $orgLine = $grn->lines->where('grnl_id',$line['id'])->first();

                if(!$orgLine)
                    throw new WebAPIException("Invalid request");

                if($orgLine->grnl_qty<$line['qty']||$line['qty']<0)
                    throw new WebAPIException("Bad Request");

                $orgLine->grnl_qty = $line['qty'];
                $total+= $orgLine->grnl_price* $line['qty'];



                DistributorStock::create([
                    'dis_id'=>$grn->dis_id,
                    'product_id'=>$orgLine->product_id,
                    'db_id'=>$orgLine->db_id,
                    'ds_credit_qty'=>$line['qty'],
                    'ds_debit_qty'=>0,
                    'ds_ref_id'=>$orgLine->getKey(),
                    'ds_ref_type'=>1,
                ]);

                $orgLine->save();
            }

            $grn->grn_confirmed_at = date('Y-m-d H:i:s');
            $grn->grn_confirmed_by = $user->getKey();
            $grn->grn_amount = $total;
            $grn->save();
            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            
            throw new WebAPIException("Something went wrong during confirmation.");
        }

        return response()->json([
            'success'=>true,
            'message'=>"Successfully confirmed the GRN."
        ]);
    }
}