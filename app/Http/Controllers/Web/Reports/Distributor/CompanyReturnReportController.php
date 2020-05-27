<?php
namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\CompanyReturn;
use App\Models\CompanyReturnLine;
use App\Models\DistributorStock;
use App\Models\GoodReceivedNote;
use App\Models\GoodReceivedNoteLine;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyReturnReportController extends ReportController {

    protected $title = "Company Return Report";

    protected $updateColumnsOnSearch = true;

    public function search($request)
    {
        $startDate = $request->input('values.s_date');
        $endDate = $request->input('values.e_date');
        $disId = $request->input('values.dis_id.value');
        $grnNumber = $request->input('values.grn_no');

        $query = DB::table('company_return AS cr')
            ->join('good_received_note AS grn','grn.grn_id','cr.grn_id')
            ->join('users AS u','u.id','grn.dis_id')
            ->select([
                'grn.grn_no',
                'grn.created_at AS grn_date',
                'cr.created_at AS return_date',
                'cr_amount',
                'grn_amount',
                'cr.cr_id',
                'grn.grn_id',
                'cr.cr_number',
                'cr_remark',
                'cr_confirmed_at'
            ]);

        if(isset($grnNumber)){
            $query->like('grn_no','LIKE',"%$grnNumber%");
        }

        if(isset($disId)){
            $query->like('dis_id',$disId);
        }

        if(isset($startDate)&&isset($endDate)){
            $query->whereDate('cr.created_at','>=',$startDate);
            $query->whereDate('cr.created_at','<=',$endDate);
        }

        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'grn_no':
                $sortBy = 'grn_no';
                break;
            case 'grn_date':
                $sortBy = 'grn.created_at';
                break;
            case 'returned_date':
                $sortBy = 'cr.created_at';
                break;
            case 'grn_amount':
                $sortBy = 'grn.grn_amount';
                break;
            case 'returned_amount':
                $sortBy = 'cr.cr_amount';
                break;
            default:
                $sortBy = 'cr.created_at';
                break;
        }

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $results = $query->get();

        $results->transform(function($row){

            $grnLines = GoodReceivedNoteLine::where('grn_id',$row->grn_id)->with(['product','distributorBatch'])->get();
            $returnLines = CompanyReturnLine::where('cr_id',$row->cr_id)->with('reason')->get();

            return [
                'return_no'=>$row->cr_number,
                'grn_no'=>$row->grn_no,
                'grn_date'=>$row->grn_date,
                'returned_date'=>$row->return_date,
                'grn_amount'=>$row->grn_amount,
                'returned_amount'=>$row->cr_amount,
                'remark'=>$row->cr_remark,
                'details'=>[
                    'title'=>$row->cr_number,
                    'lines'=>$grnLines->transform(function(GoodReceivedNoteLine $grnl) use($returnLines) {
                        /** @var \App\Models\CompanyReturnLine $returnLine */
                        $returnLine = $returnLines->where('grnl_id',$grnl->getKey())->first();

                        return [
                            'product'=>$grnl->product?[
                                "value"=>$grnl->product->product_id,
                                'label'=>$grnl->product->product_name
                            ]:[
                                'value'=>0,
                                'label'=> "DELETED"
                            ],
                            'batch'=>$grnl->distributorBatch?[
                                'value'=> $grnl->distributorBatch->getKey(),
                                'label'=> $grnl->distributorBatch->db_code
                            ]:[
                                "value"=>0,
                                "label"=>"DELETED"
                            ],
                            'reason'=> $returnLine&&$returnLine->reason?[
                                'value'=>$returnLine->reason->getKey(),
                                'label'=>$returnLine->reason->rsn_name
                            ]:[
                                'value'=>0,
                                'label'=>'Not Returned'
                            ],
                            'grnQty'=>$grnl->grnl_qty,
                            'returnQty'=>$returnLine? $returnLine->crl_qty: 0,
                            'salable'=> $returnLine? ( $returnLine->crl_salable? "Yes": "No" ): "N/A"
                        ];

                    })
                ],
                'confirmed_date'=> $row->cr_confirmed_at,
                'print_button' => $row->cr_confirmed_at? $row->cr_id: null,
                'confirm'=>  $row->cr_confirmed_at?null: $row->cr_id,
            ];
        });

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {

        $columnController->text('return_no')->setLabel('Return NO');
        $columnController->text('grn_no')->setLabel('GRN NO');
        $columnController->text('grn_date')->setLabel('GRN Date');
        $columnController->text('returned_date')->setLabel('Return Date');
        $columnController->text('confirmed_date')->setLabel('Confirmed Date');
        $columnController->number('grn_amount')->setLabel('GRN Amount');
        $columnController->number('returned_amount')->setLabel('Return Amount');
        $columnController->text('remark')->setLabel("Remark")->setSearchable(false);
        $columnController->custom('details')->setComponent('CompanyReturnDetails')->setLabel('Details')->setSearchable(false);
        $columnController->button("confirm")->setLabel("Confirm")->setLink("report/company_return/confirm");
        $columnController->button('print_button')->setLabel('Print')->setLink('report/company_return/print');

    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')]);
        $inputController->text('grn_no')->setLabel("GRN Number")->setValidations('');;
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['dis_id', 'grn_no'], ['s_date', 'e_date']]);
    }

    public function confirm(Request $request){

        $value = $request->input('value');

        /** @var CompanyReturn $companyReturn */
        $companyReturn = CompanyReturn::with('lines')->find($value);

        if(!$value)
            throw new WebAPIException("Invalid request!");


        try {

            DB::beginTransaction();

            $companyReturn->cr_confirmed_at = date('Y-m-d H:i:s');
            $companyReturn->save();


            foreach ($companyReturn->lines as $key => $line) {

                DistributorStock::create([
                    'dis_id'=>$companyReturn->dis_id,
                    'product_id'=> $line->product_id,
                    'db_id'=>$line->db_id,
                    'ds_credit_qty',
                    'ds_debit_qty'=>$line->crl_qty,
                    'ds_ref_id'=>$line->getKey(),
                    'ds_ref_type'=>9
                ]);
            }

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();
            throw new WebAPIException("Server Error Appeared! Please try again.");
        }


        return response()->json([
            'success'=>true,
            'message'=> "Successfully confirmed the company return"
        ]);
    }

    public function print(Request $request){

        $value = $request->input('value');

        $companyReturn = CompanyReturn::with(['distributor','lines','goodReceivedNote','lines.goodReceivedNoteLine','lines.product','lines.batch'])->find($value);

        if(!$companyReturn)
            throw new WebAPIException("Can not find a company return");


        $user = Auth::user();

        $data = [
            'distributor'=> $companyReturn->distributor,
            'lines'=> $companyReturn->lines,
            'goodReceivedNote'=> $companyReturn->goodReceivedNote,
            'confirmed'=> $companyReturn->cr_confirmed_at,
            'number'=> $companyReturn->cr_number,
            'createdTime'=> $companyReturn->created_at->format('Y-m-d H:i:s'),
            'confirmedTime'=> $companyReturn->cr_confirmed_at,
            'remark'=> $companyReturn->cr_remark,
            'printed_user'=> $user->name,
            'grossValue'=> $companyReturn->cr_amount,
            'pageCount'=> ceil($companyReturn->lines->count() / 31)
        ];


        $customPaper = array(0, 0, 609.00, 788.00);
        $pdf = PDF::loadView('company-return-pdf', $data);
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
