<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Area;
use App\Models\Chemist;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaCustomerTarget;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerTargetVsAchiReportController extends ReportController
{

    protected $title = "Customer Target Achievement Report";

    public function search(Request $request)
    {
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'chemist':
                $sortBy = 'chemist_id';
                break;
            case 'chemist_name':
                $sortBy = 'chemist_name';
                break;
            default:
                $sortBy = 'chemist_id';
                break;
        }

        $user = Auth::user();

        if ($user) {
            $userCode = substr($user->u_code, 0, 4);
            $area = Area::where('ar_code', $userCode)->first();
        }

        $invoice = DB::table('chemist as c')
            ->join('invoice_line as il', 'il.chemist_id', 'c.chemist_id')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi', function ($query) {
                $query->on('pi.product_id', '=', 'p.product_id');
                $query->on('pi.year', '=', DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
            })
            ->select([
                'c.chemist_id',
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.chemist_id');

        $return = DB::table('chemist as c')
            ->join('return_lines as il', 'il.chemist_id', 'c.chemist_id')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi', function ($query) {
                $query->on('pi.product_id', '=', 'p.product_id');
                $query->on('pi.year', '=', DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
            })
            ->select([
                'c.chemist_id',
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.chemist_id');

        if (isset($values['user'])) {
            // $values['user']['value']
            $products = SalesmanValidPart::where('u_id', 489)->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['month'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['month'])))->get();
            $chemists = SalesmanValidCustomer::where('u_id', 489)->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['month'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['month'])))->get();

            $invoice->whereIn('il.product_id', $products->pluck('product_id')->all());
            $invoice->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

            $return->whereIn('il.product_id', $products->pluck('product_id')->all());
            $return->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());
        }

        if (isset($values['chem_id'])) {
            $invoice->where('il.chemist_id', $values['chem_id']['value']);
            $return->where('il.chemist_id', $values['chem_id']['value']);
        }

        if (isset($values['month'])) {
            $invoice->whereDate('il.invoice_date', '>=', date('Y-m-01', strtotime($values['month'])));
            $invoice->whereDate('il.invoice_date', '<=', date('Y-m-t', strtotime($values['month'])));

            $return->whereDate('il.invoice_date', '>=', date('Y-m-01', strtotime($values['month'])));
            $return->whereDate('il.invoice_date', '<=', date('Y-m-t', strtotime($values['month'])));
        }

        if ($user->getRoll() == config('shl.area_sales_manager_type')) {
            if (isset($area->ar_code)) {
                $users = User::where('u_code', 'LIKE', '%' . $area->ar_code . '%')->get();

                $products = SalesmanValidPart::whereIn('u_id', $users->pluck('id')->all())->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['month'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['month'])))->get();
                $chemists = SalesmanValidCustomer::whereIn('u_id', $users->pluck('id')->all())->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['month'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['month'])))->get();

                $invoice->whereIn('il.product_id', $products->pluck('product_id')->all());
                $invoice->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

                $return->whereIn('il.product_id', $products->pluck('product_id')->all());
                $return->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());
            }
        }

        $grandproductnet = DB::table(DB::raw("({$invoice->toSql()}) as sub"))
            ->mergeBindings(get_class($invoice) == 'Illuminate\Database\Eloquent\Builder' ? $invoice->getQuery() : $invoice)->sum(DB::raw('bdgt_value'));

        $grandproductnet2 = DB::table(DB::raw("({$return->toSql()}) as sub"))
            ->mergeBindings(get_class($return) == 'Illuminate\Database\Eloquent\Builder' ? $return->getQuery() : $return)->sum(DB::raw('bdgt_value'));

        $grandproduct_achive = $grandproductnet - $grandproductnet2;

        $invoices = $invoice->get();
        $returns = $return->get();

        $allachivements = $invoices->concat($returns);

        $chemist = Chemist::whereIn('chemist_id', $allachivements->pluck('chemist_id')->all());
        $count = $this->paginateAndCount($chemist, $request, $sortBy);

        $results = $chemist->get();

        $chemistAchi = 0;
        $balance = 0;
        $achi = 0;
        $results->transform(function ($row) use ($invoices, $returns, $values, $chemistAchi, $balance, $achi) {

            $salesAchi = $invoices->where('chemist_id', $row->chemist_id)->sum('bdgt_value');
            $returnAchi = $returns->where('chemist_id', $row->chemist_id)->sum('bdgt_value');

            if (isset($salesAchi) && isset($returnAchi)) {
                $chemistAchi = $salesAchi - $returnAchi;
            }

            $targets = SfaCustomerTarget::where('sfa_cus_code', $row->chemist_id)->where('sfa_year', date('Y', strtotime($values['month'])))->where('sfa_month', date('m', strtotime($values['month'])))->first();

            if ((isset($targets->sfa_target) && isset($chemistAchi) && ($targets->sfa_target > 0 && $chemistAchi > 0))) {
                $balance = $targets->sfa_target - $chemistAchi;
            }

            if ((isset($targets->sfa_target) && isset($chemistAchi) && ($targets->sfa_target > 0 && $chemistAchi > 0))) {
                $achi = $chemistAchi / $targets->sfa_target * 100;
            }

            $return['chemist'] = isset($row->chemist_code) ? $row->chemist_code : '-';
            $return['chemist_name'] = isset($row->chemist_name) ? $row->chemist_name : '-';
            $return['target'] = isset($targets->sfa_target) ? number_format($targets->sfa_target, 2) : 0;
            $return['target_new'] = isset($targets->sfa_target) ? $targets->sfa_target : 0;
            $return['achi'] = isset($chemistAchi) ? number_format($chemistAchi, 2) : 0;
            $return['achi_new'] = isset($chemistAchi) ? $chemistAchi : 0;
            $return['ach_%'] = isset($achi) ? round($achi, 2) : 0;
            $return['balance'] = isset($balance) ? number_format($balance, 2) : 0;
            $return['balance_new'] = isset($balance) ? $balance : 0;
            return $return;
        });

        $resultss = $results->sortBy('target')->all();

        $row = [
            'special' => true,
            'chemist' => 'Total',
            'chemist_name' => null,
            'target' => number_format($results->sum('target_new'), 2),
            'achi' => number_format($results->sum('achi_new'), 2),
            'ach_%' => null,
            'balance' => number_format($results->sum('balance_new'), 2),
        ];

        $rownew = [
            'special' => true,
            'chemist' => 'Grand Total',
            'chemist_name' => null,
            'target' => NULL,
            'achi' => number_format($grandproduct_achive, 2),
            'ach_%' => null,
            'balance' => null,
        ];

        $resultss->push($row);
        $resultss->push($rownew);

        $sort = "chemist";

        // if ($sortBy == 'chemist') {
        //     $sort = "chemist";
        // } elseif ($sortBy == 'chemist_name') {
        //     $sort = "chemist_name";
        // }
        // if ($sortBy == 'target') {
        //     $sort = "target";
        // }

        // $data = $results->sortBy($sort)->values()->all();
        // return $data;

        return [
            'results' => $resultss,
            'count' => $count,
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text("chemist")->setLabel("Customer");
        $columnController->text("chemist_name")->setLabel("Customer Name");
        $columnController->number("target")->setLabel("Target");
        $columnController->number("achi")->setLabel("Achivement");
        $columnController->number("ach_%")->setLabel("%");
        $columnController->number("balance")->setLabel("Balance");
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown("area")->setLabel("Area")->setLink("area")->setValidations('');
        $inputController->ajax_dropdown("user")->setLabel("User")->setLink("user")->setWhere(['u_tp_id' => 10])->setValidations('');
        $inputController->ajax_dropdown("chem_id")->setLabel("Customer")->setLink("chemist")->setValidations('');
        $inputController->date("month")->setLabel("Month");

        $inputController->setStructure([["user", "chem_id", "month"]]);
    }
}
