<?php

namespace App\Http\Controllers\Web\Reports\Allocations;

use \Illuminate\Http\Request;
use App\Form\Columns\ColumnController;
use App\Form\Inputs\InputController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\InvoiceAllocation;
use App\Models\InvoiceAllocationQty;
use App\Models\SalesAllocation;
use App\Models\TmpInvoiceAllocation;
use Illuminate\Support\Facades\DB;

class InvoiceAllocationReportController extends ReportController
{

    protected $title = "Invoice Allocations Report";

    public function search(Request $request)
    {
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'team':
                $sortBy = 't.tm_name';
                break;
            case 'town':
                $sortBy = 'st.sub_twn_name';
                break;
            case 'customer':
                $sortBy = 'c.chemist_name';
                break;
            case 'number':
                $sortBy = 'il.invoice_no';
                break;
            case 'qty':
                $sortBy  = 'il.invoiced_qty';
                break;
            default:
                $sortBy = 'ia.created_at';
        }

        $teamId = $request->input('values.team.value');
        $townId = $request->input('values.town.value');
        $customer = $request->input('values.customer.value');
        $invoiceNum = $request->input('values.invoice_num');
        $startDate = $request->input('values.s_date');
        $endDate = $request->input('value.e_date');

        $query = DB::table('invoice_allocations AS ia');

        $query->join('invoice_line AS il', 'il.inv_line_id', 'ia.inv_line_id')
            ->join('chemist AS c', 'c.chemist_id', 'il.chemist_id')
            ->join('product AS p', 'p.product_id', 'il.product_id')
            ->join('sub_town AS st', 'st.sub_twn_id', 'c.sub_twn_id')
            ->join('teams AS t', 't.tm_id', 'ia.tm_id')
            ->whereNull('ia.deleted_at');

        if ($teamId) {
            $query->where('ia.tm_id', $teamId);
        }

        if ($townId) {
            $query->where('st.sub_twn_id', $townId);
        }

        if ($customer) {
            $query->where('c.chemist_id', $customer);
        }

        if ($invoiceNum) {
            $query->where(DB::raw('CONCAT(il.invoice_no,"-",il.series_id)'), 'LIKE', "%$invoiceNum%");
        }

        if ($startDate && $endDate) {
            $query->whereDate('ia.created_at', '>=', $startDate);
            $query->whereDate('ia.created_at', '<=', $endDate);
        }

        $query->select([
            't.tm_id',
            't.tm_name',
            'st.sub_twn_id',
            'st.sub_twn_name',
            'c.chemist_id',
            'c.chemist_name',
            'il.invoiced_qty',
            'il.invoice_no',
            'il.series_id',
            'ia.ia_id',
            'p.product_id',
            'p.product_name'
        ]);

        $count = $this->paginateAndCount($query, $request, $sortBy);

        $results = $query->get();

        $formatedResults = [];

        foreach ($results as $key => $result) {
            $memberQtys = InvoiceAllocationQty::with('user')->where('ia_id', $result->ia_id)->get();

            foreach ($memberQtys as $key => $memberQty) {
                $row = [];
                if ($key != 0) {
                    $row['team_rowspan'] = 0;
                    $row['town_rowspan'] = 0;
                    $row['customer_rowspan'] = 0;
                    $row['number_rowspan'] = 0;
                    $row['product_rowspan'] = 0;
                    $row['qty_rowspan'] = 0;
                    $row['user_rowspan'] = 1;
                    $row['allocated_qty_rowspan'] = 1;
                    $row['delete_button_rowspan'] = 0;
                } else {
                    $memberQtysCount = $memberQtys->count();

                    $row['team_rowspan'] = $memberQtysCount;
                    $row['town_rowspan'] = $memberQtysCount;
                    $row['customer_rowspan'] = $memberQtysCount;
                    $row['number_rowspan'] = $memberQtysCount;
                    $row['product_rowspan'] = $memberQtysCount;
                    $row['qty_rowspan'] = $memberQtysCount;
                    $row['user_rowspan'] = 1;
                    $row['allocated_qty_rowspan'] = 1;
                    $row['delete_button_rowspan'] = $memberQtysCount;
                }

                $row['team'] = [
                    'value' => $result->tm_id,
                    'label' => $result->tm_name
                ];

                $row['town'] = [
                    'value' => $result->sub_twn_id,
                    'label' => $result->sub_twn_name,
                ];

                $row['customer'] = [
                    'value' => $result->chemist_id,
                    'label' => $result->chemist_name,
                ];

                $row['number'] = $result->invoice_no;
                $row['product'] = [
                    'value' => $result->product_id,
                    'label' => $result->product_name
                ];

                $row['qty'] = $result->invoiced_qty;
                $row['user'] = [
                    'value' => $memberQty->user ? $memberQty->user->getKey() : 0,
                    'label' => $memberQty->user ? $memberQty->user->name : "DELETED",
                ];

                $row['allocated_qty'] = $memberQty->iaq_qty;

                $row['delete_button'] = $result->ia_id;



                $formatedResults[] = $row;
            }
        }

        // $row1 = [
        //     'special' => true,
        //     // 'route' => '',
        //     'allocated_qty' => round($results->sum('allocated_qty')),
        //     'qty' => round($results->sum('qty')),
        // ];

        // $formatedResults->push($row1);


        return [
            'results' => $formatedResults,
            'count' => $count
        ];
    }

    protected function setColumns(ColumnController $columnController,  $request)
    {
        $columnController->ajax_dropdown('team')->setLabel("Team");
        $columnController->ajax_dropdown('town')->setLabel("Town");
        $columnController->ajax_dropdown('customer')->setLabel("Customer");
        $columnController->text('number')->setLabel("Invoice Number");
        $columnController->ajax_dropdown("product")->setLabel("Product");
        $columnController->number('qty')->setLabel("Main Qty");
        $columnController->ajax_dropdown('user')->setLabel("MR/PS");
        $columnController->number('allocated_qty')->setLabel("Allocated Qty");
        $columnController->button("delete_button")->setLabel("Delete")->setLink("report/invoice_allocation/delete");
    }

    protected function setInputs(InputController $inputController)
    {
        $inputController->ajax_dropdown('team')->setLabel("Team")->setLink('team')->setValidations('');
        $inputController->ajax_dropdown('town')->setLabel("Town")->setLink('sub_town')->setValidations('');
        $inputController->ajax_dropdown('customer')->setLabel("Customer")->setLink('chemist')->setWhere(['sub_twn_id' => "{town}"])->setValidations('');
        $inputController->text('invoice_num')->setLabel("Invoice Number")->setValidations('');
        $inputController->date('s_date')->setLabel("From")->setValidations('');
        $inputController->date('e_date')->setLabel("To")->setValidations('');

        $inputController->setStructure([
            ['team', 'town'],
            ['customer', 'invoice_num'],
            ['s_date', 'e_date'],
        ]);
    }

    public function delete(Request $request)
    {
        $value = $request->input('value');

        InvoiceAllocation::where('ia_id', $value)->delete();

        InvoiceAllocationQty::where('ia_id', $value)->delete();

        TmpInvoiceAllocation::where('ia_id', $value)->delete();

        return response()->json([
            'success' => true,
            "message" => "You have successfully deleted the allocation.",
        ]);
    }
}
