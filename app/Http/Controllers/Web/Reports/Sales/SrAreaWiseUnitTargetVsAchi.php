<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Area;
use App\Models\Product;
use App\Models\SalesItinerary;
use App\Models\SalesItineraryDate;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaSalesOrder;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\CodeCoverage\Report\Xml\Totals;

class SrAreaWiseUnitTargetVsAchi extends ReportController
{

    protected $title = "SR Area Wise Unit Target Vs Achivement Report";

    protected $updateColumnsOnSearch = true;

    public function search(Request $request)
    {
        $values = $request->input('values');
        if (!isset($values['area'])) {
            throw new WebAPIException('Area field is required');
        }

        $invoice = DB::table('product as p')
            ->join('invoice_line as il', 'il.product_id', 'p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'il.product_id',
                'il.salesman_code',
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('il.product_id','il.salesman_code');

        $return = DB::table('product as p')
            ->join('return_lines as il', 'il.product_id', 'p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'il.product_id',
                'il.salesman_code',
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS return_qty'),
                DB::raw('SUM(IFNULL(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('il.product_id','il.salesman_code');

        if (isset($values['pro_id'])) {
            $invoice->where('il.product_id', $values['pro_id']['value']);
            $return->where('il.product_id', $values['pro_id']['value']);
        }

        if (isset($values['user'])) {
            $user = User::where('id',$values['user']['value'])->first();
            $invoice->where('il.salesman_code', $user->u_code);
            $return->where('il.salesman_code', $user->u_code);
        }

        if (isset($values['month'])) {
            $invoice->whereDate('il.invoice_date', '>=', date('Y-m-01', strtotime($values['month'])));
            $invoice->whereDate('il.invoice_date', '<=', date('Y-m-t', strtotime($values['month'])));

            $return->whereDate('il.invoice_date', '>=', date('Y-m-01', strtotime($values['month'])));
            $return->whereDate('il.invoice_date', '<=', date('Y-m-t', strtotime($values['month'])));
        }

        $invoices = $invoice->get();
        $returns = $return->get();

        $allachivements = $invoices->concat($returns);

        $query = Product::with('principal');
        $query->whereIn('product_id', $allachivements->pluck('product_id')->all());
        $count = $this->paginateAndCount($query, $request, 'product_id');

        if (isset($values['pro_id'])) {
            $query->where('product_id', $values['pro_id']['value']);
        }
        $products = $query->get();

        $area = Area::where('ar_id', $values['area']['value'])->first();
        $users = User::where('u_code', 'LIKE', '%' . $area->ar_code . '%')->get();

        $products->transform(function ($val) use ($values, $users, $invoices, $returns) {

            $return['agency'] = $val->principal ? $val->principal->principal_name : '';
            $return['pro_code'] = $val->product_code;
            $return['pro_name'] = $val->product_name;
            $return['range'] = "";
            $return['achi'] = "";

            foreach ($users as $key => $user) {

                $query = SfaTarget::query();
                $query->where('u_id', $user->id);
                $query->where('trg_year', date('Y', strtotime($values['month'])));
                $query->where('trg_month', date('m', strtotime($values['month'])));
                $query->latest();
                $target_user = $query->first();

                $target_user = SfaTargetProduct::where('sfa_trg_id', $target_user['sfa_trg_id'])->where('product_id', $val->product_id)->first();

                $salesQtyAchi = $invoices->where('product_id', $val->product_id)->where('salesman_code', $user->u_code)->sum('net_qty');
                $returnQtyAchi = $returns->where('product_id', $val->product_id)->where('salesman_code', $user->u_code)->sum('return_qty');

                $salesAchi = $invoices->where('product_id', $val->product_id)->where('salesman_code', $user->u_code)->sum('bdgt_value');
                $returnAchi = $returns->where('product_id', $val->product_id)->where('salesman_code', $user->u_code)->sum('bdgt_value');

                if (isset($salesQtyAchi) && isset($returnQtyAchi)) {
                    $productQtyAchi = $salesQtyAchi - $returnQtyAchi;
                }

                if (isset($salesAchi) && isset($returnAchi)) {
                    $productAchi = $salesAchi - $returnAchi;
                }

                if (isset($productQtyAchi) && isset($target_user['stp_qty']) && ($productQtyAchi > 0 && $target_user['stp_qty'] > 0)) {
                    $achiQty = $target_user['stp_qty'] / $productQtyAchi * 100;
                }

                $return['sum_tar_qty_' . $user->u_code] = $target_user['stp_qty'] ? $target_user['stp_qty'] : 0;
                $return['sum_tar_val_' . $user->u_code] = $target_user['stp_amount'] ? number_format($target_user['stp_amount'], 2) : 0;
                $return['sum_tar_val_new_' . $user->u_code] = $target_user['stp_amount'] ? $target_user['stp_amount'] : 0;

                $return['sum_act_qty_' . $user->u_code] = isset($productQtyAchi) ? $productQtyAchi : 0;
                $return['sum_act_val_' . $user->u_code] = isset($productAchi) ? number_format($productAchi, 2) : 0;

                $return['sum_act_val_new_' . $user->u_code] = isset($productAchi) ? $productAchi : 0;

                $return['achi_%_' . $user->u_code] = isset($achiQty) ? round($achiQty, 2) : 0;
                $return['achi_%_new_' . $user->u_code] = isset($achiQty) ? $achiQty : 0;
            }

            return $return;
        });

        $row['special'] = true;
        $row['agency'] = 'Totals';
        $row['pro_code'] = NULL;
        $row['pro_name'] = NULL;
        $row['range'] = NULL;
        $row['achi'] = NULL;

        foreach ($users as $key => $user) {
            $row['sum_tar_qty_' . $user->u_code] = number_format($products->sum('sum_tar_qty_' . $user->u_code));
            $row['sum_tar_val_' . $user->u_code] = number_format($products->sum('sum_tar_val_new_' . $user->u_code));

            $row['sum_act_qty_' . $user->u_code] = number_format($products->sum('sum_act_qty_' . $user->u_code));
            $row['sum_act_val_' . $user->u_code] = number_format($products->sum('sum_act_val_new_' . $user->u_code));

            $row['achi_%_' . $user->u_code] = round($products->sum('achi_%_new_' . $user->u_code));
        }

        $products->push($row);

        return [
            'results' => $products,
            'count' => $count
        ];
    }

    protected function getAdditionalHeaders($request)
    {
        $values = $request->input('values');

        if (isset($values['area'])) {
            $area = Area::where('ar_id', $values['area']['value'])->first();
            $query = User::query();
            $query->where('u_code', 'LIKE', '%' . $area->ar_code . '%');

            if (isset($values['user'])) {
                $query->where('id',$values['user']['value']);
            }

            $users = $query->get();
        }

        $columns = array();
        $column_array[] = array(
            'title' => "",
            'colSpan' => 5
        );

        if (isset($users)) {
            foreach ($users as $key => $user) {
                $column_array[] = array(
                    'title' => $area->ar_name . ' | ' . $user->name,
                    'colSpan' => 5
                );
            }
        }

        array_push($columns, $column_array);
        return $columns;
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $values = $request->input('values');

        $columnController->text('agency')->setLabel('Agency Name');
        $columnController->text('pro_code')->setLabel('Product Code');
        $columnController->text('pro_name')->setLabel('Product Name');
        $columnController->text('range')->setLabel('Range');
        $columnController->text('achi')->setLabel('Achi');

        if (isset($values['area'])) {
            $area = Area::where('ar_id', $values['area']['value'])->first();
            $query = User::query();
            $query->where('u_code', 'LIKE', '%' . $area->ar_code . '%');

            if (isset($values['user'])) {
                $query->where('id',$values['user']['value']);
            }

            $users = $query->get();

            foreach ($users as $key => $user) {
                $columnController->number('sum_tar_qty_' . $user->u_code)->setLabel(' Sum of Target  Qty');
                $columnController->number('sum_tar_val_' . $user->u_code)->setLabel(' Sum of Target  Value');

                $columnController->number('sum_act_qty_' . $user->u_code)->setLabel(' Sum of Actual Qty');
                $columnController->number('sum_act_val_' . $user->u_code)->setLabel(' Sum of Actual Value');

                $columnController->number('achi_%_' . $user->u_code)->setLabel('Ach%');
            }
        }
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('area')->setLabel('Area')->setLink('area');
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type')])->setValidations('');
        $inputController->ajax_dropdown('pro_name')->setLabel('Product')->setLink('product')->setValidations('');
        $inputController->date('month')->setLabel('month')->setLink('s_date');
        $inputController->date('e_date')->setLabel('To')->setLink('e_date');

        $inputController->setStructure([
            ['area', 'user'],
            ['pro_name', 'month']
        ]);
    }
}
