<?php
namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\GoodReceivedNote;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReportController extends ReportController
{
    protected $title = "Purchase Order Report";

    public function search($request)
    {

        $values = $request->input('values');

        $query = PurchaseOrder::query();

        if (isset($values['po_number'])) {
            $query->where('po_number', 'LIKE', "%{$values['po_number']}%");
        }

        if (isset($values['dis_id'])) {
            $query->where('dis_id', $values['dis_id']['value']);
        }

        if (isset($values['sr_id'])) {
            $query->where('sr_id', $values['sr_id']);
        }

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $query->where('dis_id', $user->getKey());
        }

        if (isset($values['s_date']) && isset($values['e_date'])) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        $query->with(['distributor', 'salesRep', 'lines', 'lines.product']);

        $results = $query->get();

        $results->transform(function ($result) {
            $color = [];
            $grn = GoodReceivedNote::where('po_id',$result->po_id)->first();

            if (isset($result->integrated_at) && !isset($grn)) {
                $color = [
                    'background' => '#4d5463',//black
                    'border' => '1px solid #fff',
                ];
            } else if(!isset($result->integrated_at) && !isset($grn)) {
                $color = [
                    'background' => '#f2916b',//orange
                    'border' => '1px solid #fff',
                ];
            }

            if (isset($grn)) {
                $color = [
                    'background' => '#346ce3',//blue
                    'border' => '1px solid #fff',
                ];
            }

            if (isset($grn->grn_confirmed_at)) {
                $color = [
                    'background' => '#0fb85e',//green
                    'border' => '1px solid #fff',
                ];
            }

            return [
                'po_number' => $result->po_number,
                'po_number_style' => $color,
                'dis_id' => $result->distributor ? [
                    'value' => $result->distributor->id,
                    'label' => $result->distributor->name,
                ] : null,
                'dis_id_style' => $color,
                'sr_id' => $result->salesRep ? [
                    'value' => $result->salesRep->id,
                    'label' => $result->salesRep->name,
                ] : null,
                'sr_id_style' => $color,
                'po_amount_undefiend' => $result->po_amount,
                'po_amount_undefiend_style' => $color,
                'po_amount' => number_format($result->po_amount, 2),
                'po_amount_style' => $color,
                'created_at' => $result->created_at->format('Y-m-d'),
                'created_at_style' => $color,
                'integrated_at' => $result->integrated_at,
                'integrated_at_style' => $color,
                'confirm_link' => $result->integrated_at ? null : [
                    'label' => "Confirm",
                    'link' => '/distributor/purchase_order/purchase_order_confirm/' . str_replace('/', '_', $result->po_number),
                    'react' => true,
                ],
                'confirm_link_style' => $color,
                'details' => [
                    'title' => $result->po_number,
                    'products' => $result->lines->map(function ($line) {
                        return [
                            'product' => $line->product ? [
                                'label' => $line->product->product_name,
                                'value' => $line->product->product_id,
                            ] : [
                                'label' => 'DELETED',
                                'value' => 0,
                            ],
                            'pack_size' => $line->product->pack_size,
                            'qty' => $line->pol_qty,
                            'amount' => number_format($line->pol_amount, 2),
                            'price' => number_format($line->pol_price, 2),
                        ];
                    }),
                ],
                'details_style' => $color,
            ];
        });

        $newRow = [];
        $newRow = [
            'special' => true,
            'po_number' => 'Total',
            'dis_id' => null,
            'sr_id' => null,
            'po_amount' => number_format($results->sum('po_amount_undefiend'), 2),
            'created_at' => null,
            'integrated_at' => null,
            'details' => null,
            'confirm_link' => null,
        ];

        $results->push($newRow);

        return [
            'results' => $results,
            'count' => 0,
        ];

    }

    protected function getAdditionalHeaders($request){

        $columns = [[
             [
                  "title"=>"Orange: Not confirmed order | Black: Confirmed order | Blue: Invoiced orders in the IFS system | Green: GRN orders in the distributor system ",
                  "colSpan"=>8
             ]
        ]];

        return $columns;
   }

    public function setColumns(ColumnController $columnController, Request $request)
    {

        $columnController->text('po_number')->setLabel('Order No.');
        $columnController->ajax_dropdown('dis_id')->setLabel('Distributor');
        $columnController->ajax_dropdown('sr_id')->setLabel('Distributor Sales Rep.');
        $columnController->number('po_amount')->setLabel('Amount');
        $columnController->text('created_at')->setLabel('Created Time');
        $columnController->text('integrated_at')->setLabel('Integrated Time');
        $columnController->custom("details")->setLabel("Products")->setComponent('PurchaseOrderDetails');
        $columnController->link('confirm_link')->setLabel('Confirm');
    }

    public function setInputs($inputController)
    {
        $inputController->text('po_number')->setLabel('Order No.')->setValidations('');
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_type')]);
        $inputController->ajax_dropdown('sr_id')->setLabel('Sales Rep')->setLink('user')->setWhere(['u_tp_id' => config('shl.distributor_sales_rep_type'), 'dis_id' => '{dis_id}']);
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('To');
        $inputController->setStructure([['po_number', 'dis_id', 'sr_id'], ['s_date', 'e_date']]);
    }

}
