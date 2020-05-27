<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\DistributorCustomer;
use App\Models\DistributorInvoice;
use App\Models\GoodReceivedNote;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class RDOrderVsInvoiceReportController extends ReportController
{
    protected $title = "RD  Order Vs Invoices Report";

    public function search($request)
    {
        $values = $request->input('values', []);

        $query = DB::table('distributor_sales_order as dso')
            ->select([
                'dsop.product_id',
                'p.product_code',
                'p.product_name',
                'dsr.u_code AS dsr_code',
                'dsr.name AS dsr_name',
                'dis.name AS dis_name',
                'dsr.id AS dsr_id',
                'dis.id AS dis_id',
                'dso.order_date',
                'pr.principal_name',
                'dsop.sales_qty',
                'dsop.price',
                'dso.dist_order_id',
                'dil.dil_qty',
                'dil.dil_unit_price',
                'dso.dc_id',
                'dso.order_no'
            ])
            ->join('distributor_sales_order_products as dsop', 'dsop.dist_order_id', 'dso.dist_order_id')
            ->join('users AS dsr', 'dsr.id', '=', 'dso.u_id')
            ->join('users AS dis', 'dis.id', '=', 'dso.dis_id')
            ->join('product as p', 'p.product_id', 'dsop.product_id')
            ->join('principal as pr', 'pr.principal_id', 'p.principal_id')
            ->join('distributor_invoice AS di', 'di.dist_order_id', '=', 'dso.dist_order_id')
            // ->where('dso.order_date','>=',$values['s_date'])
            // ->where('dso.order_date','<=',$values['e_date'])
            ->leftJoin('distributor_invoice_line as dil', function ($join) {
                $join->on('dil.di_id', '=', 'di.di_id')
                    ->on('dil.product_id', '=', 'dsop.product_id');
            })
            ->whereNull('dso.deleted_at')
            ->whereNull('di.deleted_at')
            ->whereNull('dil.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pr.deleted_at')
            ->whereNull('dsop.deleted_at');





        if (isset($values['dsr_id'])) {
            $query->where('dsr.id', $values['dsr_id']['value']);
        }

        if (isset($values['dis_id'])) {
            $query->where('dis.id', $values['dis_id']['value']);
        }

        if (isset($values['pro_id'])) {
            $query->where('dsop.product_id', $values['pro_id']['value']);
        }




        if (isset($values['s_date']) && isset($values['e_date'])) {
            // $query->where('dso.order_date', '>=', $values['s_date']);
            // $query->where('dso.order_date', '<=', $values['e_date']);

            $query->whereBetween( DB::raw( 'DATE(dso.order_date)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        $grandortot = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('price * sales_qty'));

        $grandinvtot = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('dil_qty * dil_unit_price'));

        $grandlostot = $grandortot - $grandinvtot;

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $query->where('dis.id', $user->getKey());
        }


        $count = $this->paginateAndCount($query, $request, 'dis_id');


        $results = $query->get();

        $results->transform(function ($val) {

            $dc = DistributorCustomer::where('dc_id',$val->dc_id)->first();

            $sales_tot = $val->price * $val->sales_qty;
            $tot_value = $val->dil_qty * $val->dil_unit_price;

            if (isset($val->sales_qty) && isset($val->dil_qty))
                $losted_qty = $val->sales_qty - $val->dil_qty;

            if (isset($val->price) && isset($val->dil_unit_price))
                // $losted_val = $val->price - $tot_value;
                $losted_val = $sales_tot - $tot_value;

            return [
                'date' => date('Y-m-d', strtotime($val->order_date)),
                'dis_name' => $val->dis_name,
                'agency_name' => $val->principal_name,
                'pro_code' => $val->product_code,
                'pro_name' => $val->product_name,
                'pack_size' => 0,
                'cus_name' => $dc->dc_name,
                'sr_code' => $val->dsr_code,
                'sr_name' => $val->dsr_name,
                'so_no' => $val->order_no,
                'order_qty' => isset($val->sales_qty) ? $val->sales_qty : 0,
                'order_value' => isset($val->price) && isset($val->sales_qty) ? number_format($val->sales_qty * $val->price, 2) : 0,
                'order_value_new' => isset($val->price) && isset($val->sales_qty) ? $sales_tot : 0,
                'inv_qty' => isset($val->dil_qty) ? $val->dil_qty : 0,
                'inv_value' => isset($val->dil_unit_price) && isset($val->dil_qty) ? number_format($tot_value, 2) : 0,
                'inv_value_new' => isset($val->dil_unit_price) && isset($val->dil_qty) ? $tot_value : 0,
                'losed_qty' => isset($losted_qty) ? $losted_qty : 0,
                'losed_value' => isset($losted_val) ? number_format($losted_val, 2) : 0,
                'losed_value_new' => isset($losted_val) ? $losted_val : 0
            ];
        });

        $row = [
            'special' => true,
            'date' => 'Total',
            'dis_name' => NULL,
            'agency_name' => NULL,
            'pro_code' => NULL,
            'pro_name' => NULL,
            'pack_size' => NULL,
            'cus_name' => NULL,
            'sr_code' => NULL,
            'sr_name' => NULL,
            'so_no' => NULL,
            'order_qty' => $results->sum('order_qty'),
            'order_value' => number_format($results->sum('order_value_new'), 2),
            'inv_qty' => $results->sum('inv_qty'),
            'inv_value' => number_format($results->sum('inv_value_new'), 2),
            'losed_qty' => $results->sum('losed_qty'),
            'losed_value' => number_format($results->sum('losed_value_new'), 2)
        ];

        $rownew = [];
        $rownew['special'] = true;
        $rownew['date'] = 'Grand Total';
        $rownew['dis_name'] = NULL;
        $rownew['agency_name'] = NULL;
        $rownew['pro_code'] = NULL;
        $rownew['pro_name'] = NULL;
        $rownew['pack_size'] = NULL;
        $rownew['cus_name'] = NULL;
        $rownew['sr_code'] = NULL;
        $rownew['sr_name'] = NULL;
        $rownew['so_no'] = NULL;
        $rownew['order_qty'] = NULL;
        $rownew['order_value'] = number_format($grandortot, 2);
         $rownew['inv_qty'] = NULL;
        $rownew['inv_value'] = number_format($grandinvtot, 2);
        $rownew['losed_qty'] = NULL;
        $rownew['losed_value'] = number_format($grandlostot,2);



        $results->push($row);
        $results->push($rownew);

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $values = $request->input('values', []);

        $columnController->text('date')->setLabel('Date');
        $columnController->text('dis_name')->setLabel('Distributor Name');
        $columnController->text('agency_name')->setLabel('Agency Name');
        $columnController->text('pro_code')->setLabel('Product Code');
        $columnController->text('pro_name')->setLabel('Product Name');
        $columnController->text('pack_size')->setLabel('Pack Size');
        $columnController->text('cus_name')->setLabel('Customer');
        $columnController->text('sr_code')->setLabel('SR Code');
        $columnController->text('sr_name')->setLabel('SR Name');
        $columnController->text('so_no')->setLabel('Sales Order No');
        $columnController->number('order_qty')->setLabel('Order Qty');
        $columnController->number('order_value')->setLabel('Order Value');
        $columnController->number('inv_qty')->setLabel('Invoiced Qty');
        $columnController->number('inv_value')->setLabel('Invoiced Value');
        $columnController->number('losed_qty')->setLabel('Losed Sales Qty');
        $columnController->number('losed_value')->setLabel('Losed Sales Value');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')]);
        $inputController->ajax_dropdown('dsr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{dis_id}']);
        $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['dsr_id', 'dis_id', 'pro_id'], ['s_date', 'e_date']]);
    }
}
