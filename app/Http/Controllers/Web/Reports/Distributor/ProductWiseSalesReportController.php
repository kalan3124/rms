<?php
namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Http\Controllers\Web\Reports\ReportController;

use Illuminate\Http\Request;
use App\Form\Columns\ColumnController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\DistributorStock;
use App\Models\DistributorCustomer;
use App\Models\Principal;
use App\Models\Product;
use App\Models\User;

class ProductWiseSalesReportController extends ReportController{

    protected $title = "Product Wise Sales";
    protected $updateColumnsOnSearch = true;

    public function search( Request $request){
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            default:
                $sortBy = 'product_code';
                break;
        }

        $invoiceQuery = DB::table('distributor_invoice as di')
            ->select([
                'p.product_id',
                'p.principal_id',
                'p.product_code',
                'p.product_name',
                'pc.principal_name',
                DB::raw('SUM(dil.dil_qty) as sale_qty'),
                'di.dsr_id',
                'di.dis_id',
                'di.dc_id',
                'di.created_at',
            ])
            ->join('distributor_invoice_line as dil', 'dil.di_id', 'di.di_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->join('distributor_customer as dc', 'di.dc_id', 'dc.dc_id')
            ->join('principal as pc', 'p.principal_id', 'pc.principal_id')
            ->whereNull('di.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
            ->groupBy('p.product_id','p.principal_id')
            ;

        $returnQuery = DB::table('distributor_return as dir')
            ->select([
                'p.product_id',
                'p.principal_id',
                'p.product_code',
                'p.product_name',
                'pc.principal_name',
                DB::raw('0 - dil.dri_qty as sale_qty'),
                'dir.dsr_id',
                'dir.dis_id',
                'dir.dc_id',
                'dir.created_at',
            ])
            ->join('distributor_return_item as dil', 'dil.dis_return_id', 'dir.dis_return_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->join('distributor_customer as dc', 'dir.dc_id', 'dc.dc_id')
            ->join('principal as pc', 'p.principal_id', 'pc.principal_id')
            ->whereNull('dir.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
            ->groupBy('p.product_id','p.principal_id')
            ;


        if(isset($values['dis_id'])){
            $invoiceQuery->where('di.dis_id',$values['dis_id']['value']);
            $returnQuery->where('dir.dis_id',$values['dis_id']['value']);
        }

        if (isset($values['pr_id'])) {
            $invoiceQuery->where('p.principal_id', $values['pr_id']['value']);
            $returnQuery->where('p.principal_id', $values['pr_id']['value']);
        }

        if (isset($values['s_date']) && isset($values['e_date'])) {
            $invoiceQuery->whereBetween(DB::raw('DATE(di.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
            $returnQuery->whereBetween(DB::raw('DATE(dir.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }


        $invoices = $invoiceQuery->get();
        $returns = $returnQuery->get();


        $sales = $invoices->concat($returns);

        $results = $sales;

        $query = User::where('u_tp_id', 15); //sels ref type
        $users = $query->get();
        // return $results;

        $results->transform(function ($val) use ($sales,$users) {
            $return['p_code']         = $val->product_code;
            $return['p_name']         = $val->product_name;
            $return['ag_name']        = $val->principal_name;

            $total = 0;
            foreach ($users as $key => $row) {//sels ref type
                $salesQty                 = $sales->where('dsr_id', $row->id)->where('principal_id', $val->principal_id)->where('product_id', $val->product_id)->sum('sale_qty');
                $return['sr_' . $row->id] = number_format($salesQty, 0);
                $total += $salesQty;
            }

            $return['total_qty'] = isset($total) ? number_format($total, 0) : 0;

            return $return;
        });

        // return $results;

        $row['special'] = true;
        $row['ag_name'] = 'Total';
        $row['p_code'] = null;
        $row['p_name'] = null;

        foreach ($users as $key => $value) {
            $row['sr_'.$value->id] = number_format($results->sum('sr_'.$value->id), 0);
        }

        $row['total_qty'] = number_format($results->sum('total_qty'), 0);

        $results->push($row);

        return [
            'results' => $results,
            // 'values' => $values,
            'count' => 0
        ];
    }


    public function setColumns(ColumnController $columnController, Request $request){
        $values = $request->values;
        $query = User::where('u_tp_id', 15); //sels ref type
        $users = $query->get();

        $columnController->text('ag_name')->setLabel('Agency Name');
        $columnController->text('p_code')->setLabel('Product Code');
        $columnController->text('p_name')->setLabel('Name');

        foreach ($users as $key => $user) {
            $columnController->text('sr_' . $user->id)->setLabel($user->u_code.'|'.$user->name);
        }

        $columnController->number('total_qty')->setLabel('Total Qty');
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_type')]);
        $inputController->ajax_dropdown('pr_id')->setLabel('Principal')->setLink('principal');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('Date');

        $inputController->setStructure([
            ['dis_id','pr_id'],
            ['s_date','e_date']
        ]);
    }

}
