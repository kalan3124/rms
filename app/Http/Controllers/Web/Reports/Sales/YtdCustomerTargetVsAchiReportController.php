<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Chemist;
use App\Models\Issue;
use App\Models\SfaCustomerTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exceptions\WebAPIException;
use App\Models\Area;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class YtdCustomerTargetVsAchiReportController extends ReportController
{

    protected $title = "Ytd Customer Target Achievement Report";
    protected $updateColumnsOnSearch = true;

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
            case 'sub_name':
                $sortBy = 'sub_twn_id';
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
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'c.chemist_id',
                'c.chemist_name',
                DB::raw('YEAR(il.invoice_date) as year'),
                DB::raw('MONTH(il.invoice_date) as month'),
                DB::raw('DATE(il.invoice_date) as date'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.chemist_id', DB::raw('YEAR(il.invoice_date)'), DB::raw('MONTH(il.invoice_date)'));

        $return = DB::table('chemist as c')
            ->join('return_lines as il', 'il.chemist_id', 'c.chemist_id')
            ->join('product as p', 'p.product_id', 'il.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'c.chemist_id',
                'c.chemist_name',
                DB::raw('YEAR(il.invoice_date) as year'),
                DB::raw('MONTH(il.invoice_date) as month'),
                DB::raw('DATE(il.invoice_date) as date'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value')
            ])
            ->whereNull('c.deleted_at')
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('c.chemist_id', DB::raw('YEAR(il.invoice_date)'), DB::raw('MONTH(il.invoice_date)'));

        if (isset($values['user'])) {
            $products = SalesmanValidPart::where('u_id', $values['user']['value'])->whereDate('from_date', '<=', date('Y-m-d', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-d', strtotime($values['e_date'])))->get();
            $chemists = SalesmanValidCustomer::where('u_id', $values['user']['value'])->whereDate('from_date', '<=', date('Y-m-d', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-d', strtotime($values['e_date'])))->get();

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
            $invoice->whereDate('il.invoice_date', '>=', date('Y-m-d', strtotime($values['s_date'])));
            $invoice->whereDate('il.invoice_date', '<=', date('Y-m-d', strtotime($values['e_date'])));

            $return->whereDate('il.invoice_date', '>=', date('Y-m-d', strtotime($values['s_date'])));
            $return->whereDate('il.invoice_date', '<=', date('Y-m-d', strtotime($values['e_date'])));
        }

        if ($user->getRoll() == config('shl.area_sales_manager_type')) {
            if (isset($area->ar_code)) {
                $users = User::where('u_code', 'LIKE', '%' . $area->ar_code . '%')->get();

                $products = SalesmanValidPart::whereIn('u_id', $users->pluck('id')->all())->whereDate('from_date', '<=', date('Y-m-d', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-d', strtotime($values['e_date'])))->get();
                $chemists = SalesmanValidCustomer::whereIn('u_id', $users->pluck('id')->all())->whereDate('from_date', '<=', date('Y-m-d', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-d', strtotime($values['e_date'])))->get();

                $invoice->whereIn('il.product_id', $products->pluck('product_id')->all());
                $invoice->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

                $return->whereIn('il.product_id', $products->pluck('product_id')->all());
                $return->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());
            }
        }

        $invoices = $invoice->get();
        $returns = $return->get();

        // return $invoices;

        $allachivements = $invoices->concat($returns);

        $chemist = Chemist::with('sub_town', 'chemist_class')->whereIn('chemist_id', $allachivements->pluck('chemist_id')->all());
        $count = $this->paginateAndCount($chemist, $request, $sortBy);

        $results = $chemist->get();

        $begin = new \DateTime(date('Y-m-01', strtotime($values['s_date'])));
        $end = new \DateTime(date('Y-m-d', strtotime($values['e_date'])));

        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($begin, $interval, $end);

        $chemistAchi = 0;
        $balance = 0;
        $achi = 0;
        $results->transform(function ($row) use ($invoices, $returns, $values, $chemistAchi, $balance, $achi, $period) {

            foreach ($period as $key => $month) {
                $salesAchi = $invoices->where('year', $month->format('Y'))->where('month', $month->format('m'))->where('chemist_id', $row->chemist_id)->sum('bdgt_value');
                $returnAchi = $returns->where('year', $month->format('Y'))->where('month', $month->format('m'))->where('chemist_id', $row->chemist_id)->sum('bdgt_value');

                if (isset($salesAchi) && isset($returnAchi))
                    $chemistAchi = $salesAchi - $returnAchi;

                $return['month_' . $month->format('m')] = isset($chemistAchi) ? number_format($chemistAchi, 2) : 0;
                $return['month_new' . $month->format('m')] = isset($chemistAchi) ? $chemistAchi : 0;
            }

            $return['chemist'] = isset($row->chemist_code) ? $row->chemist_code : '-';
            $return['chemist_name'] = isset($row->chemist_name) ? $row->chemist_name : '-';
            $return['sub_name'] = isset($row->sub_town) ? $row->sub_town->sub_twn_name : '-';
            $return['class'] = isset($row->chemist_class) ? $row->chemist_class->chemist_class_name : '-';
            return $return;
        });

        $row = [
            'special' => true,
            'chemist' => 'Total',
            'chemist_name' => NULL,
            'sub_name' => NULL,
            'class' => NULL
        ];

        $begin = new \DateTime(date('Y-m-01', strtotime($values['s_date'])));
        $end = new \DateTime(date('Y-m-d', strtotime($values['e_date'])));

        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($begin, $interval, $end);

        foreach ($period as $key => $month) {
            $row['month_' . $month->format('m')] = number_format($results->sum('month_new' . $month->format('m')), 2);
        }

        $results->push($row);

        return [
            'results' => $results,
            'count' => $count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $values = $request->input('values', []);

        $columnController->text("chemist")->setLabel("Customer");
        $columnController->text("chemist_name")->setLabel("Customer Name");
        $columnController->number("sub_name")->setLabel("Sub Town");
        $columnController->number("class")->setLabel("Customer Class");

        if (isset($values['s_date']) && isset($values['e_date'])) {

            $begin = new \DateTime(date('Y-m-01', strtotime($values['s_date'])));
            $end = new \DateTime(date('Y-m-d', strtotime($values['e_date'])));

            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($begin, $interval, $end);

            foreach ($period as $key => $month) {
                $columnController->number('month_' . $month->format('m'))->setLabel($month->format('M'));
            }
        }
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown("area")->setLabel("Area")->setLink("area")->setValidations('');
        $inputController->ajax_dropdown("user")->setLabel("User")->setLink("user")->setWhere(['u_tp_id' => 10])->setValidations('');
        $inputController->ajax_dropdown("chem_id")->setLabel("Customer")->setLink("chemist")->setValidations('');
        $inputController->date("s_date")->setLabel("From");
        $inputController->date("e_date")->setLabel("To");

        $inputController->setStructure([["user", "chem_id"], ['s_date', 'e_date']]);
    }
}
