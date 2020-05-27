<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Area;
use App\Models\Chemist;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaCustomerTarget;
use App\Models\SubTown;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TownWiseTargetVsAchivReportController extends ReportController
{
    protected $title = "Town Wise Target Vs Achievement Report";

    public function search(Request $request)
    {
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'town_name':
                $sortBy = 'sub_twn_id';
                break;
            default:
                $sortBy = 'sub_twn_id';
                break;
        }

        $user = Auth::user();

        if ($user) {
            $userCode = substr($user->u_code, 0, 4);
            $area = Area::where('ar_code', $userCode)->first();
        }

        $invoice = DB::table('sub_town as sr')
            ->join('chemist as c', 'c.sub_twn_id', 'sr.sub_twn_id')
            ->join('invoice_line as il', 'il.chemist_id', 'c.chemist_id')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'c.sub_twn_id',
                // 'sr.sub_twn_id',
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('sr.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.sub_twn_id');

        $return = DB::table('sub_town as sr')
            ->join('chemist as c', 'c.sub_twn_id', 'sr.sub_twn_id')
            ->join('return_lines as il', 'il.chemist_id', 'c.chemist_id')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'c.sub_twn_id',
                // 'sr.sub_twn_id',
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('sr.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.sub_twn_id');

        if (isset($values['user'])) {
            $products = SalesmanValidPart::where('u_id', $values['user']['value'])->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['s_date'])))->get();
            $chemists = SalesmanValidCustomer::where('u_id', $values['user']['value'])->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['s_date'])))->get();

            $invoice->whereIn('il.product_id', $products->pluck('product_id')->all());
            $invoice->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

            $return->whereIn('il.product_id', $products->pluck('product_id')->all());
            $return->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());
        }

        if (isset($values['sub_town'])) {
            $invoice->whereIn('c.sub_twn_id', $values['sub_town']['value']);
            $return->whereIn('c.sub_twn_id', $values['sub_town']['value']);
        }

        if (isset($values['chem_id'])) {
            $invoice->where('il.chemist_id', $values['chem_id']['value']);
            $return->where('il.chemist_id', $values['chem_id']['value']);
        }

        if (isset($values['s_date'])) {
            $invoice->whereDate('il.invoice_date', '>=', date('Y-m-01', strtotime($values['s_date'])));
            $invoice->whereDate('il.invoice_date', '<=', date('Y-m-t', strtotime($values['s_date'])));

            $return->whereDate('il.invoice_date', '>=', date('Y-m-01', strtotime($values['s_date'])));
            $return->whereDate('il.invoice_date', '<=', date('Y-m-t', strtotime($values['s_date'])));
        }

        if ($user->getRoll() == config('shl.area_sales_manager_type')) {
            if (isset($area->ar_code)) {
                $users = User::where('u_code', 'LIKE', '%' . $area->ar_code . '%')->get();

                $products = SalesmanValidPart::whereIn('u_id', $users->pluck('id')->all())->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['s_date'])))->get();
                $chemists = SalesmanValidCustomer::whereIn('u_id', $users->pluck('id')->all())->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['s_date'])))->get();

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

         $grandproduct_achive=$grandproductnet-$grandproductnet2;


        $invoices = $invoice->get();
        $returns = $return->get();

        $allachivements = $invoices->concat($returns);

        $route = SubTown::whereIn('sub_twn_id', $allachivements->pluck('sub_twn_id')->all());
        $count = $this->paginateAndCount($route, $request, 'sub_twn_id');

        $results = $route->get();

        $routeAchi = 0;
        $balance = 0;
        $achi = 0;
        $results->transform(function ($row) use ($invoices, $returns, $values, $routeAchi, $balance, $achi) {

            $salesAchi = $invoices->where('sub_twn_id', $row->sub_twn_id)->sum('bdgt_value');
            $returnAchi = $returns->where('sub_twn_id', $row->sub_twn_id)->sum('bdgt_value');

            if (isset($salesAchi) && isset($returnAchi)) {
                $routeAchi = $salesAchi - $returnAchi;
            }

            $chemists = Chemist::where('sub_twn_id', $row->sub_twn_id)->get();
            $targets = SfaCustomerTarget::whereIn('sfa_cus_code', $chemists->pluck('chemist_id')->all())->where('sfa_year', date('Y', strtotime($values['s_date'])))->where('sfa_month', date('m', strtotime($values['s_date'])))->sum('sfa_target');

            if ((isset($targets) && isset($routeAchi) && ($targets > 0 && $routeAchi > 0))) {
                $balance = $targets - $routeAchi;
            }

            if ((isset($targets) && isset($routeAchi) && ($targets > 0 && $routeAchi > 0))) {
                $achi = $routeAchi / $targets * 100;
            }

            $return['town_name'] = isset($row->sub_twn_name) ? $row->sub_twn_name : '';
            $return['target'] = isset($targets) ? number_format($targets, 2) : 0;
            $return['target_new'] = isset($targets) ? $targets : 0;
            $return['achi'] = isset($routeAchi) ? number_format($routeAchi, 2) : 0;
            $return['achi_new'] = isset($routeAchi) ? $routeAchi : 0;
            $return['ach_%'] = isset($achi) ? round($achi, 2) : 0;
            $return['balance'] = isset($balance) ? number_format($balance, 2) : 0;
            $return['balance_new'] = isset($balance) ? $balance : 0;
            return $return;
        });

        $row = [
            'special' => true,
            'town_name' => 'Total',
            'target' => number_format($results->sum('target_new'), 2),
            'achi' => number_format($results->sum('achi_new'), 2),
            'ach_%' => null,
            'balance' => number_format($results->sum('balance_new'), 2),
        ];

        $rownew = [
            'special' => true,
            'town_name' => 'Grand Total',
            'target' => NULL,
            'achi' => number_format($grandproduct_achive,2),
            'ach_%' => null,
            'balance' => NULL,
        ];

        $results->push($row);
        $results->push($rownew);

        return [
            'results' => $results,
            'count' => $count,
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('town_name')->setLabel('Town');
        $columnController->number('target')->setLabel('Target');
        $columnController->number('achi')->setLabel('Achivement');
        $columnController->number('ach_%')->setLabel('%');
        $columnController->number('balance')->setLabel('Balance Value');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('sub_town')->setLabel('Town')->setLink('sub_town')->setValidations('');
        $inputController->ajax_dropdown('chem_id')->setLabel('Chemist')->setLink('chemist')->setValidations('');
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id' => '10'])->setValidations('');
        $inputController->date('s_date')->setLabel('Month');
        $inputController->date('e_date')->setLabel('To');

        $inputController->setStructure([
            ['user', 'sub_town'], ['chem_id', 's_date'],
        ]);
    }
}
