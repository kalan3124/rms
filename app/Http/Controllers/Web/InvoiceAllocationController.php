<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceAllocation;
use App\Models\InvoiceAllocationQty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\TeamUser;
use App\Models\TmpInvoiceAllocation;

class InvoiceAllocationController extends Controller {

    public function loadData(Request $request){
        $validation = Validator::make($request->all(),[
            'team'=>'required|array',
            'team.value'=>'required|exists:teams,tm_id'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please select a representative.");
        }

        $teamId = $request->input('team.value');

        $teamMembers = TeamUser::with('user')->where('tm_id',$teamId)->get();

        $teamMembers = $teamMembers->filter(function($teamMember){
            return !!$teamMember->user;
        })->map(function($teamMember){
            return [
                'id'=>$teamMember->user->id,
                'name'=>$teamMember->user->name,
                'code'=>$teamMember->user->u_code,
                'value'=>0
            ];
        })
        ->values();

        return response()->json([
            'success'=>true,
            'selected'=>[],
            'mode'=>"include",
            'teamMembers'=>$teamMembers,
            'productSelected'=>[]
        ]);
    }

    public function search(Request $request){

        $values = $request->input('terms');
        $page = $request->input('page');
        $perPage = $request->input('perPage');

        $query = Invoice::query();

        $query->join('chemist','chemist.chemist_id','invoice.chemist_id');
        $query->join('sub_town','sub_town.sub_twn_id','chemist.sub_twn_id');

        if(isset($values['invoice_num'])){
            $query->where(DB::raw('CONCAT(invoice.invoice_no,"-",invoice.invoice_series)'),"LIKE","%{$values['invoice_num']}%");
        }

        if(isset($values['from_date'])&& isset($values['to_date'])){
            $query->whereDate('invoice.created_date','>=',$values['from_date']);
            $query->whereDate('invoice.created_date','<=',$values['to_date']);
        }

        if(isset($values['chemist'])){
            $query->where('invoice.chemist_id',$values['chemist']['value']);
        }

        if(isset($values['sub_town'])){
            $query->where('chemist.sub_twn_id',$values['sub_town']['value']);
        }

        $count = $query->count();

        $query->take($perPage);

        $query->skip(($page-1)*$perPage);

        $invoices = $query->get();

        $invoices->transform(function($invoice){

            return [
                'id'=>$invoice->inv_head_id,
                'invoice_num'=>$invoice->invoice_no."-".$invoice->invoice_series,
                'date'=>$invoice->created_date,
                'chemist'=>$invoice->customer?$invoice->chemist_name:"DELETED",
                "town"=>$invoice->sub_twn_name,
                "type"=>"Invoice",
                "amount"=>$invoice->gross_amount
            ];
        });

        return response()->json([
            'success'=>true,
            'results'=>$invoices,
            'count'=>$count
        ]);


    }

    public function searchProducts(Request $request){
        $keyword = $request->input('keyword');
        $page = $request->input('page',1);
        $perPage = $request->input('perPage',10);
        $invoices = $request->input('invoices');

        $invoices = collect($invoices);
        $returns = collect([]);

        $invoiceLinesQuery = DB::table('invoice_line AS il')
            ->join('product AS p','p.product_id','il.product_id')
            ->where(function($query)use($keyword){
                $query->orWhere('il.invoice_no','LIKE',"%$keyword%");
                $query->orWhere('p.product_code','LIKE',"%$keyword%");
                $query->orWhere('p.product_name','LIKE',"%$keyword%");
            })
            ->whereIn(DB::raw('CONCAT(il.invoice_no,"-",il.series_id)'),$invoices->pluck('invoice_num'));

        $returnLinesQuery = DB::table('return_lines AS rl')
            ->join('product AS p','p.product_id','rl.product_id')
            ->where(function($query)use($keyword){
                $query->orWhere('rl.invoice_no','LIKE',"%$keyword%");
                $query->orWhere('p.product_code','LIKE',"%$keyword%");
                $query->orWhere('p.product_name','LIKE',"%$keyword%");
            })
            ->whereIn(DB::raw('CONCAT(rl.invoice_no,"-",rl.series_id)'),$invoices->pluck('invoice_num'));
        
        $invoicesCount = $invoiceLinesQuery->count();
        $returnsCount = $returnLinesQuery->count();

        $invoices = $invoiceLinesQuery->orderBy('p.product_name')
            ->get();

        $returns = $returnLinesQuery->orderBy('p.product_name')->get();
        
        $invoices->transform(function($invoice){
            return [
                'id'=>$invoice->inv_line_id,
                'invoice_num'=>$invoice->invoice_no.'-'.$invoice->series_id,
                'product_code'=>$invoice->product_code,
                'qty'=>$invoice->invoiced_qty,
                'product_name'=>$invoice->product_name,
                'is_invoice'=>1
            ];
        });

        $returns->transform(function($return){
            return [
                'id'=>$return->return_line_id,
                'invoice_num'=>$return->invoice_no.'-'.$return->series_id,
                'product_code'=>$return->product_code,
                'qty'=>$return->invoiced_qty,
                'product_name'=>$return->product_name,
                'is_invoice'=>0
            ];
        });

        $invoices = $invoices->concat($returns);

        $invoices = $invoices->forPage($page,$perPage);

        return response()->json([
            'success'=>true,
            'results'=>$invoices,
            'count'=>$invoicesCount+$returnsCount
        ]);

    }

    public function save(Request $request){
        $validation = Validator::make($request->all(),[
            'team'=>'required',
            'team.value'=>'required|exists:teams,tm_id',
            'mode'=>'required',
            'selected'=>'required|array',
            'selected.*.id'=>'required|numeric'
        ]);

        if($validation->fails()){
            throw new WebAPIException("Invalid request. Please check again your inputs.");
        }

        $team = $request->input('team.value');
        $mode = $request->input('mode');
        $selected = $request->input('selected');
        $invoiceLineId = $request->input('productChecked.id');
        $isInvoice = $request->input('productChecked.is_invoice');
        $teamMembers = $request->input('teamMembers');

        try{
            DB::beginTransaction();

            $invoiceAllocation = InvoiceAllocation::create([
                'inv_line_id'=> $isInvoice? $invoiceLineId:null,
                'return_line_id'=>$isInvoice?null: $invoiceLineId,
                'tm_id'=>$team
            ]);

            foreach ($teamMembers as $key => $teamMember) {
                InvoiceAllocationQty::create([
                    'tm_id'=>$team,
                    'ia_id'=>$invoiceAllocation->getKey(),
                    'u_id'=>$teamMember['id'],
                    'iaq_qty'=>$teamMember['value']
                ]);

                TmpInvoiceAllocation::create([
                    'ia_id'=>$invoiceAllocation->getKey(),
                    'inv_line_id'=> $isInvoice? $invoiceLineId:null,
                    'return_line_id'=>$isInvoice?null: $invoiceLineId,
                    'u_id'=>$teamMember['id'],
                    'tia_qty'=>$teamMember['value'],
                ]);
            }

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();

            throw new WebAPIException("Server error occured! Please contact your system vendor.");
        }

        return response()->json([
            'success'=>true,
            'message'=>"You have successfully allocated your invoices."
        ]);
    }
}