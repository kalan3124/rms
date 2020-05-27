<?php
namespace App\Http\Controllers\Web\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\CompanyReturn;
use App\Models\CompanyReturnLine;
use App\Models\GoodReceivedNote;
use App\Models\GoodReceivedNoteLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyReturnController extends Controller {
    public function load(Request $request){
        $grnNumber = $request->input('grnNumber');

        /** @var GoodReceivedNote $grn */
        $grn = GoodReceivedNote::with(['lines','lines.product','lines.distributorBatch'])->where('grn_no',$grnNumber)->first();

        if(!$grn){
            throw new WebAPIException("Can not find a GRN for this number");
        }

        if(!$grn->grn_confirmed_at){
            throw new WebAPIException("GRN is not confirmed yet");
        }

        $companyReturn = CompanyReturn::where('grn_id',$grn->getKey())->first();

        if($companyReturn){
            throw new WebAPIException("Company Return is exist for this GRN.");
        }

        return response()->json([
            'success'=> true,
            'number'=> CompanyReturn::generateNumber($grn->dis_id),
            'lines'=>$grn->lines->map(function(GoodReceivedNoteLine $grnl){
                return [
                    'product'=>$grnl->product?[
                        "value"=> $grnl->product->getKey(),
                        "label"=> $grnl->product->product_name,
                    ]:[
                        "value"=>0,
                        "label"=>"DELETED"
                    ],
                    "batch"=>$grnl->distributorBatch?[
                        "value"=> $grnl->distributorBatch->getKey(),
                        'label'=> $grnl->distributorBatch->db_code
                    ]:[
                        "value"=>0,
                        "label"=> "DELETED"
                    ],
                    'qty'=> 0,
                    'price'=>(float) $grnl->distributorBatch->db_price,
                    'expire'=>$grnl->distributorBatch->db_expire,
                    'orgQty'=>$grnl->grnl_qty,
                    "reason"=> null,
                    "salable"=> false,
                    'id'=> $grnl->getKey()
                ];
            })
        ]);
    }

    public function save(Request $request){
        $validator = Validator::make($request->all(),[
            'grnNumber'=>'required',
            'lines'=>'required|array',
            'lines.*.product'=>'required|array',
            'lines.*.batch'=>'required|array',
        ]);

        if($validator->fails()){
            throw new WebAPIException("Can not validate your request. Reasons and GRN Number Is Required");
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $lines = $request->input('lines');
        $grnNumber = $request->input('grnNumber');
        $remark = $request->input('remark');

        $grn = GoodReceivedNote::with('lines')->where('grn_no',$grnNumber)->first();

        if(!$grn){
            throw new WebAPIException("GRN is not exist for supplied number");
        }

        $companyReturn = CompanyReturn::where('grn_id',$grn->getKey())->first();

        if($companyReturn){
            throw new WebAPIException("Company Return is exist for this GRN.");
        }

        try {
            DB::beginTransaction();

            $companyReturn = CompanyReturn::create([
                'cr_remark'=> empty($remark)?"N/A": $remark,
                'grn_id'=>$grn->getKey(),
                'u_id'=>$user->getKey(),
                'cr_amount'=>0,
                'dis_id'=> $grn->dis_id,
                'cr_number'=> CompanyReturn::generateNumber($grn->dis_id)
            ]);

            $amount = 0;
            foreach ($lines as $line){
                if($line['qty']){
                    if(!$line['reason']){
                        throw new WebAPIException("Reason is required.");
                    }
                    CompanyReturnLine::create([
                        'cr_id'=>$companyReturn->getKey(),
                        'grnl_id'=>$line['id'],
                        'product_id'=>$line['product']['value'],
                        'db_id'=>$line['batch']['value'],
                        'crl_qty'=>$line['qty'],
                        'rsn_id'=>$line['reason']['value'],
                        'crl_salable'=>$line['salable']?1:0,
                    ]);

                    $amount += $line['qty']*$line['price'];
                }
            }

            $companyReturn->cr_amount = $amount;

            $companyReturn->save();

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'success'=> true,
            'message'=> "Successfully added the company return"
        ]);

    }
}
