<?php

namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\DistributorSalesOrder;
use App\Models\DistributorSalesOrderProduct;
use App\Models\SfaSalesOrder;
use App\Models\SfaSalesOrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderReportController extends ReportController
{
    protected $title = "Sales Order Report";

    public function search($request)
    {

        $values = $request->input('values');

        $query = DistributorSalesOrder::with(['user', 'distributorCustomer', 'salesOrderProducts', 'salesOrderProducts.product', 'area']);

        $query->whereBetween( DB::raw( 'DATE(order_date)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);

        $grandtot = DB::table(DB::raw("({$query->toSql()}) as sub"))
        ->mergeBindings(get_class($query) == 'Illuminate\Database\Eloquent\Builder' ? $query->getQuery() : $query)->sum(DB::raw('sales_order_amt'));


        if (isset($values['order_number'])) {
            $query->where('order_no', 'LIKE', "%{$values['order_number']}%");
        }

        if (isset($values['user'])) {
            $query->where('u_id', $values['user']['value']);
        }

        if (isset($values['ar_id'])) {
            $query->where('ar_id', $values['ar_id']['value']);
        }

        if (isset($values['customer'])) {
            $query->where('dc_id', $values['customer']['value']);
        }

        if (isset($values['order_mode'])) {
            $query->where('order_mode', $values['order_mode']['value']);
        }

        if (isset($values['dis_id'])) {
            $query->where('dis_id', $values['dis_id']['value']);
        }

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $query->where('dis_id', $user->getKey());
        }
        // return config('shl.distributor_type');

        if (isset($values['s_date'])&&isset($values['e_date'])) {
          $query->whereBetween( DB::raw( 'DATE(order_date)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }


        $count = $this->paginateAndCount($query, $request, 'order_no');

        $results = $query->get();

        $results->transform(function (DistributorSalesOrder $salesOrder) {

            return [
                'order_no' => $salesOrder->order_no,
                'contract' => $salesOrder->contract,
                'u_id' => $salesOrder->user ? [
                    'value' => $salesOrder->user->getKey(),
                    'label' => $salesOrder->user->name
                ] : [
                    'value' => 0,
                    'label' => "DELETED"
                ],
                'customer' => $salesOrder->distributorCustomer ? [
                    'value' => $salesOrder->distributorCustomer->getKey(),
                    'label' => $salesOrder->distributorCustomer->dc_name
                ] : [
                    'value' => 0,
                    'label' => "DELETED"
                ],
                'order_date' => $salesOrder->order_date,
                'order_date_server'=> $salesOrder->created_at->format('Y-m-d H:i:s'),
                'latitude' => $salesOrder->latitude,
                'longitude' => $salesOrder->longitude,
                'battery_lvl' => $salesOrder->battery_lvl,
                'app_version' => $salesOrder->app_version,
                'is_invoiced' => $salesOrder->is_invoiced ? 'Invoiced' : 'Not Invoiced',
                'details' => [
                    'title' => $salesOrder->order_no,
                    'products' => $salesOrder->salesOrderProducts->map(function (DistributorSalesOrderProduct $salesOrderProduct) {

                        return [
                            'product' => $salesOrderProduct->product ? [
                                'value' => $salesOrderProduct->product->getKey(),
                                'label' => $salesOrderProduct->product->product_name
                            ] : [
                                'value' => 0,
                                'label' => "DELETED"
                            ],
                            'qty' => $salesOrderProduct->sales_qty
                        ];
                    })
                ],
                'amount_unformated' => $salesOrder->sales_order_amt,
                'amount' => number_format($salesOrder->sales_order_amt, 2),
                'amount_new' => $salesOrder->sales_order_amt,
                'invoice_link' => $salesOrder->is_invoiced ? null : [
                    'label' => "Invoice",
                    'link' => '/distributor/dist_sales_order/create_invoice/' . str_replace('/', '_', $salesOrder->order_no),
                    'react' => true
                ]
            ];
        });

        // $grandTotal = $results->sum('sales_order_amt');
        $newRow = [];
        $newRow  = [
            'special' => true,
            'order_no' => 'Total',
            'contract' => NULL,
            'u_id' => NULL,
            'customer' => NULL,
            'order_date' => NULL,
            'order_date_server'=>NULL,
            'latitude' => NULL,
            'longitude' => NULL,
            'battery_lvl' => NULL,
            'app_version' => NULL,
            'is_invoiced' => NULL,
            'details' => NULL,
            'amount' => number_format($results->sum('amount_unformated'), 2),
            'invoice_link' => NULL
        ];

        $newGrand = [];
        $newGrand = [
            'special' => true,
            'order_no' => 'Grand Total',
            'contract' => NULL,
            'u_id' => NULL,
            'customer' => NULL,
            'order_date' => NULL,
            'order_date_server'=>NULL,
            'latitude' => NULL,
            'longitude' => NULL,
            'battery_lvl' => NULL,
            'app_version' => NULL,
            'is_invoiced' => NULL,
            'details' => NULL,
            'amount' => number_format($grandtot, 2),
            'invoice_link' => NULL
        ];


        $results->push($newRow);
        $results->push($newGrand);

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {

        $columnController->text('order_no')->setLabel('Order No.');
        $columnController->text('contract')->setLabel('Contract');
        $columnController->ajax_dropdown('u_id')->setLabel('User');
        $columnController->ajax_dropdown('customer')->setLabel('Customer');
        $columnController->date('order_date')->setLabel('Tab Date');
        $columnController->date('order_date_server')->setLabel('Server Date');
        $columnController->text('latitude')->setLabel('Latitude');
        $columnController->text('longitude')->setLabel('Longitude');
        $columnController->text('battery_lvl')->setLabel('Battery Level');
        $columnController->text("app_version")->setLabel("App Version");
        $columnController->text('is_invoiced')->setLabel('Invoiced Status');
        $columnController->custom("details")->setLabel("Products")->setComponent('SalesOrderDetails');
        $columnController->number("amount")->setLabel("Amount");
        $columnController->link('invoice_link')->setLabel('Invoice');
    }

    public function setInputs($inputController)
    {
        $inputController->text('order_number')->setLabel('Order Number')->setValidations('');
        $inputController->ajax_dropdown('ar_id')->setLabel('Area')->setLink('area')->setValidations('');
        $inputController->ajax_dropdown('user')->setLabel('Dsr')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type')])->setValidations('');
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')])->setValidations('');
        $inputController->ajax_dropdown('customer')->setLabel('Customer')->setLink('distributor_customer')->setWhere(['dis_id' => '{dis_id}'])->setValidations('');
        $inputController->date('s_date')->setLabel("From")->setValidations('');
        $inputController->date('e_date')->setLabel("To")->setValidations('');
        $options = [
            0 => 'Planned',
            1 => 'UnPlanned'
        ];
        $inputController->select('order_mode')->setLabel('Order Mode')->setOptions($options);

        $inputController->setStructure([['user', 'dis_id', 'customer', 'order_number'], ['order_mode', 'ar_id', 's_date', 'e_date']]);
    }
}
