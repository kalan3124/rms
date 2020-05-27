<?php
namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use App\Models\DistributorInvoice;
use App\Models\DistributorSalesOrder;
use App\Traits\SalesTerritory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Auth;

class TargetController extends Controller
{

    use SalesTerritory;

    public function getSrTarget(Request $request)
    {
        $user = Auth::user();

        $dayAchi = $this->makeQuery($user->id, date('Y-m-d'), date('Y-m-d'));
        $monthAchi = $this->makeQuery($user->id, date('Y-m-01'), date('Y-m-d'));

        $invoice = DistributorInvoice::where('dsr_id', $user->id)->whereDate('created_at', '>=', date('Y-m-d'))->whereDate('created_at', '<=', date('Y-m-d'))->count();
        $sales_orders = DistributorSalesOrder::where('u_id', $user->id)->whereDate('order_date', '>=', date('Y-m-d'))->whereDate('order_date', '<=', date('Y-m-d'))->count();

        $targetInfo = [
            'dayAchievement' => isset($dayAchi) ? $dayAchi : 0,
            'dayTarget' => 0,
            'monthAchievement' => isset($monthAchi) ? $monthAchi : 0,
            'monthTarget' => 0,
            'totalInvoices' => isset($invoice) ? $invoice : 0,
            'totalSalesOrders' => isset($sales_orders) ? $sales_orders : 0,
        ];

        return response()->json([
            "result" => true,
            "data" => $targetInfo,
        ]);
    }

    protected function makeQuery($user, $fromDate, $toDate)
    {

        $invoiceQuery = DB::table('distributor_invoice as di')
            ->select([
                'di.dsr_id',
                'di.dis_id',
                'di.created_at',
                'dil.dil_unit_price',
                'dil.dil_qty',
                'p.product_code',
                'p.product_name',
                'p.product_id',
                DB::raw('SUM(dil.dil_unit_price * dil.dil_qty) as sale_amount'),
            ])
            ->join('distributor_invoice_line as dil', 'dil.di_id', 'di.di_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->where('di.dsr_id', $user)
            ->whereBetween(DB::raw('DATE(di.created_at)'), [$fromDate, $toDate])
            ->whereNull('di.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
            ->groupBy('di.dsr_id');

        $returnQuery = DB::table('distributor_return as di')
            ->select([
                'di.dsr_id',
                'di.dis_id',
                'di.created_at',
                'dil.dri_price',
                'dil.dri_qty',
                'p.product_code',
                'p.product_name',
                'p.product_id',
                DB::raw('SUM(dil.dri_price * dil.dri_qty) as sale_return_amount'),
            ])
            ->join('distributor_return_item as dil', 'dil.dis_return_id', 'di.dis_return_id')
            ->join('product as p', 'p.product_id', 'dil.product_id')
            ->where('di.dsr_id', $user)
            ->whereBetween(DB::raw('DATE(di.created_at)'), [$fromDate, $toDate])
            ->whereNull('di.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('dil.deleted_at')
            ->groupBy('di.dsr_id');

        $invoices = $invoiceQuery->get();
        $returns = $returnQuery->get();

        $invoice = 0;
        $return = 0;
        foreach ($invoices as $key => $val) {
            $invoice += $val->sale_amount;
        }

        foreach ($returns as $key => $val) {
            $return += $val->sale_return_amount;
        }

        $total = $invoice - $return;

        return $total;

    }

}
