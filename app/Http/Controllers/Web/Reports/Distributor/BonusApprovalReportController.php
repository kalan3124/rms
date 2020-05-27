<?php
namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Exceptions\WebAPIException;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Bonus;
use App\Models\BonusDistributor;
use App\Models\BonusExclude;
use App\Models\DistributorInvoice;
use App\Models\DistributorInvoiceBonusLine;
use App\Models\DistributorInvoiceLine;
use App\Models\DistributorInvoiceUnapprovedBonusLine;
use App\Models\DistributorSalesOrder;
use App\Models\DistributorStock;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BonusApprovalReportController extends ReportController {
    protected $title = 'Bonus Approval Report';

    public function search(\Illuminate\Http\Request $request)
    {

        $invoiceNumber = $request->input('values.inv_number');
        $soNumber = $request->input('values.so_number');
        $distributor = $request->input('values.distributor.value');
        $dsr = $request->input('values.dsr.value');
        $startDate = $request->input('values.s_date');
        $endDate = $request->input('values.e_date');
        $status = $request->input('values.status.value');

        /** @var Builder $query */
        $query = DistributorInvoice::whereNotNull('di_approve_requested_at');

        $query->with(['distributor','distributorSalesRep','customer','unapprovedBonusLines','lines','lines.product','lines.batch']);

        if(!empty($invoiceNumber)){
            $query->where('di_number','LIKE','%'.$invoiceNumber.'%');
        }

        if(!empty($soNumber)){
            $salesOrders = DistributorSalesOrder::where('order_no','LIKE','%'.$soNumber.'%')->get();

            $query->whereIn('dist_order_id',$salesOrders->pluck('dist_order_id')->all());
        }

        if(!empty($distributor)){
            $query->where('dis_id',$distributor);
        }

        if(!empty($dsr)){
            $query->where('dsr_id',$dsr);
        }

        if(!empty($startDate)&&!empty($endDate)){
            $query->whereDate('created_at','>=',$startDate);
            $query->whereDate('created_at','<=',$endDate);
        }

        if(isset($status)){
            if($status)
                $query->whereNotNull('di_approved_at');
            else
                $query->whereNull('di_approved_at');

        }

        $count = $this->paginateAndCount($query,$request,'created_at');

        /** @var DistributorInvoice[]|Collection $results */
        $results = $query->get();

        $results->transform(function(DistributorInvoice $invoice){
            return [
                'distributor'=>$invoice->distributor?[
                    'label'=>$invoice->distributor->name,
                    'value'=>$invoice->distributor->getKey()
                ]:[
                    'value'=>0,
                    'label'=>'N/A'
                ],
                'dsr'=>$invoice->distributorSalesRep?[
                    'label'=>$invoice->distributorSalesRep->name,
                    'value'=>$invoice->distributorSalesRep->getKey()
                ]:[
                    'label'=>'N/A',
                    'value'=>0
                ],
                'customer'=> $invoice->customer?[
                    'label'=>$invoice->customer->dc_name,
                    'value'=>0
                ]:[
                    'label'=>'N/A',
                    'value'=>0
                ],
                'dist_order_id'=>$invoice->salesOrder?$invoice->salesOrder->order_no:"N/A",
                'di_number'=>$invoice->di_number,
                'di_amount'=>$invoice->di_amount,
                'di_approve_requested_at'=>$invoice->di_approve_requested_at,
                'di_approved_at'=>$invoice->di_approved_at,
                'details'=> $invoice->di_approved_at?null: [
                    'invoiceNumber'=>$invoice->di_number,
                    'invoiceId'=>$invoice->getKey(),
                    'lines'=>$invoice->lines->map(function(DistributorInvoiceLine $line){
                        return [
                            'product'=>$line->product?[
                                'label'=>$line->product->product_name,
                                'value'=>$line->product->getKey()
                            ]:[
                                'label'=>'N/A',
                                'value'=>0
                            ],
                            'batch'=>$line->batch?[
                                'label'=>$line->batch->db_code,
                                'value'=>$line->batch->getKey()
                            ]:[
                                'label'=>'N/A',
                                'value'=>0
                            ],
                            'qty'=>$line->dil_qty,
                            'discount'=>$line->dil_discount_percent
                        ];
                    }),
                    'bonusLines'=>$this->generateBonus($invoice)
                ]
            ];
        });

        return [
            'count'=>$count,
            'results'=>$results
        ];
    }

    protected function setColumns(\App\Form\Columns\ColumnController $columnController, \Illuminate\Http\Request $request)
    {
        $columnController->ajax_dropdown('distributor')->setLabel("Distributor")->setSearchable(false);
        $columnController->ajax_dropdown('dsr')->setLabel("Sales Rep")->setSearchable(false);
        $columnController->ajax_dropdown('customer')->setLabel("Customer")->setSearchable(false);
        $columnController->text('dist_order_id')->setLabel('So Number');
        $columnController->text('di_number')->setLabel("Invoice Number");
        $columnController->text('di_amount')->setLabel("Amount");
        $columnController->date('di_approve_requested_at')->setLabel("Requested Date");
        $columnController->date('di_approved_at')->setLabel("Approved Date");
        $columnController->custom('details')->setLabel("Approve")->setComponent('BonusApprovalDetails');
    }

    protected function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('so_number')->setLabel('So No.')->setValidations('');
        $inputController->text('inv_number')->setLabel('Inv No.')->setValidations('');
        $inputController->ajax_dropdown('distributor')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')]);
        $inputController->ajax_dropdown('dsr')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{distributor}']);
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->select('status')->setLabel('Status')->setOptions([1=>'Approved',0=>'Not Approved']);
        $inputController->setStructure([
            ['so_number','inv_number','status'],
            ['distributor','dsr'],
            ['s_date','e_date']
        ]);
    }

    
    /**
     * Generating the bonus for a given sales order
     *
     * @param DistributorInvoice $invoice
     * @return Collection
     */
    private function generateBonus(DistributorInvoice $invoice){

        $lines = $invoice->lines;
        $disId = $invoice->dis_id;

        $distributorBonuses = BonusDistributor::where('dis_id',$disId)->get();

        $bonuses = Bonus::whereDate('bns_start_date','<=',$invoice->created_at->format('Y-m-d'))
            ->whereDate('bns_end_date','>=',$invoice->created_at->format('Y-m-d'))
            ->where(function($query) use($distributorBonuses) {
                $query->orWhereIn('bns_id',$distributorBonuses->pluck('bns_id')->all());
                $query->orWhere('bns_all',1);
            })
            ->with(['freeProducts','freeProducts.product','products','ratios','excludes'])
            ->get();

        $bonuses->transform(function(Bonus $bonus) use ( $lines) {
            $productIds = $bonus->products->pluck('product_id')->all();

            $qty = $lines->whereIn('product_id',$productIds)->sum('dil_qty');

            $exists = false;
            $purchaseQty = 0;
            $freeQty = 0;

            foreach ($bonus->ratios as $key => $ratio) {
                if($ratio->bnsr_min<=$qty&&$ratio->bnsr_max>=$qty){
                    $exists = true;
                    $purchaseQty = $ratio->bnsr_purchase;
                    $freeQty = $ratio->bnsr_free;
                }
            }

            if(!$exists)
                return null;

            $freeProducts = [];

            foreach ($bonus->freeProducts as $key => $bonusFreeProduct) {
                if(isset($bonusFreeProduct->product))
                    $freeProducts[$bonusFreeProduct->product->getKey()] = [
                        'qty'=>0,
                        'orgQty'=>0,
                        'value'=>$bonusFreeProduct->product->getKey(),
                        'label'=>$bonusFreeProduct->product->product_name
                    ];
            }

            $qty = floor(($freeQty/$purchaseQty)*$qty);

            if(!$qty)
                return null;

            return [
                'label'=>$bonus->bns_name,
                'qty'=>$qty,
                'products'=>$freeProducts,
                'id'=>$bonus->getKey(),
                'excludes'=>$bonus->excludes->map(function(BonusExclude $bonusExclude){
                    return $bonusExclude->bnse_bns_id;
                })
            ];
        });

        $bonuses = $bonuses->filter(function($row){return !!$row;})->values();

        $bonusLines = $invoice->unapprovedBonusLines;

        $bonuses->transform(function(&$item) use ($bonusLines) {

            foreach ($item['products'] as $key => $product) {
                /** @var DistributorInvoiceUnapprovedBonusLine[] */
                $bonusEffectedLines = $bonusLines->where('bns_id',$item['id'])->where('product_id',$product['value']);

                if($bonusEffectedLines->count()){

                    foreach ($bonusEffectedLines as $bnsKey => $bonusEffectedLine) {
                        $item['products'][$key]['qty'] = $bonusEffectedLine->diubl_qty;
                        $item['products'][$key]['orgQty'] = $bonusEffectedLine->diubl_qty;
                    }
                }

            }
            
            return $item;
        });


        return $bonuses;
    }

    public function approve(Request $request){
        $user = Auth::user();

        $invoiceId = $request->input('invId');
        $bonusLines = $request->input('bonusLines');

        /** @var DistributorInvoice $invoice */
        $invoice = DistributorInvoice::find($invoiceId);

        if(!$invoice || !$bonusLines || !is_array($bonusLines))
            throw new WebAPIException("Invalid request!");


        try {
            DB::beginTransaction();

            $invoice->di_approved_at = date('Y-m-d H:i:s');
            $invoice->di_approved_by = $user->getKey();
            $invoice->save();

            foreach ($bonusLines as $key => $bonusLine) {
                $id = $bonusLine['id'];

                foreach ($bonusLine['products'] as $key => $line) {

                    $bonusQty = $line['qty']?:0;
                    if($bonusQty>0){
                        $stock = DistributorStock::checkStock($invoice->dis_id,$line['value']);

                        $product = Product::find($line['value']);

                        if(!$product)
                            throw new WebAPIException("Invalid request");

                        $productCode = $product->product_code;

                        if($stock<$bonusQty)
                            throw new WebAPIException("Bonus product '$productCode' has not enough stock.");


                        $stocks =  DB::table('distributor_stock AS ds')
                            ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                            ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.db_id'])
                            ->where('ds.dis_id',$invoice->dis_id)
                            ->where('db.product_id',$product->getKey())
                            ->whereDate('db.db_expire','>=',date('Y-m-d'))
                            ->groupBy('db.db_id')
                            ->orderBy('db.db_expire')
                            ->having('stock','>','0')
                            ->get();

                        if($stocks->count()<=0)
                            throw new WebAPIException("Bonus Product '$productCode' has not enough stock.");
                        $index = 0;
                        while($bonusQty){
                            $stock = $stocks->get($index);

                            $qty = 0;
                            if($stock->stock>=$bonusQty){
                                $qty = $bonusQty;
                            } else {
                                $qty = $stock->stock;
                            }

                            $bonusQty-= $qty;

                            $bonusLine = DistributorInvoiceBonusLine::create([
                                'di_id'=>$invoice->getKey(),
                                'product_id'=>$product->product_id,
                                'db_id'=>$stock->db_id,
                                'dibl_unit_price'=>Product::getPriceForDistributor($product->getKey(),$stock->db_id),
                                'dibl_qty'=>$qty,
                                'bns_id'=>$id
                            ]);

                            DistributorStock::create([
                                'dis_id'=>$invoice->dis_id,
                                'product_id'=>$product->product_id,
                                'db_id'=>$stock->db_id,
                                'ds_credit_qty'=>0,
                                'ds_debit_qty'=>$qty,
                                'ds_ref_id'=>$bonusLine->getKey(),
                                'ds_ref_type'=>6,
                            ]);

                            $index++;

                            if($index>$stocks->count()-1&&$bonusQty){
                                throw new WebAPIException("Stock has changed during processing time for '$productCode' and not enough stock. Please click 'Search' button to load again the invoicing view");
                            }
                        }

                    }

                }

            }

            DB::commit();
        } catch (\Exception $e){
            DB::rollBack();

            throw $e;
        }

        return response()->json([
            'success'=>true,
            'message'=>'Successfully approved the bonus and added to stock',
        ]);
    }
}