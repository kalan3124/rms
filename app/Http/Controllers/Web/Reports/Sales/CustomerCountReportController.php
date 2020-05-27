<?php

namespace App\Http\Controllers\Web\Reports\Sales;

use App\Exceptions\WebAPIException;
use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\InvoiceLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerCountReportController extends ReportController
{
    protected $title = "Customer Count Report";

    protected $updateColumnsOnSearch = true;

    public function search(Request $request)
    {

        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'pro_code':
                $sortBy = 'p.product_code';
                break;
            case 'pro_name':
                $sortBy = 'p.product_name';
                break;
            default:
                $sortBy = 'p.product_code';
                break;
        }

        if (!isset($values['user'])) {
            throw new WebAPIException('User Field is requierd');
        }

        $fromdate = $values['s_date'] . ' 00:00:00';
        $todate = $values['e_date'] . ' 23:59:59';

        $query = DB::table('product as p')
            ->select('p.product_id', 'p.product_code', 'p.product_name', 'svp.from_date', 'svp.to_date', 'svp.u_id')
            ->join('salesman_valid_parts as svp', 'svp.product_id', 'p.product_id')
            ->where('svp.u_id', $values['user']['value'])
            ->where('svp.from_date', '<=', $fromdate)
            ->where('svp.to_date', '>=', $todate)
            ->whereNull('p.deleted_at')
            ->whereNull('svp.deleted_at')
            ->groupBy('svp.product_id');

        $queryCus = DB::table('salesman_valid_customer as svc')
            ->whereNull('svc.deleted_at')
            ->where('svc.u_id', $values['user']['value'])
            ->where('svc.from_date', '<=', $fromdate)
            ->where('svc.to_date', '>=', $todate)
            ->groupBy('svc.chemist_id')->get();

        if (isset($values['pro_id'])) {
            $query->where('p.product_id', $values['pro_id']['value']);
        }

        $count = $this->paginateAndCount($query, $request, $sortBy);
        $results = $query->get();

        /**Full Period */
        $begin = new \DateTime(date('Y-m-01', strtotime($values['s_date'])));
        $end = new \DateTime(date('Y-m-t', strtotime($values['e_date'])));

        $interval = \DateInterval::createFromDateString('1 month');
        $period = new \DatePeriod($begin, $interval, $end);
        /**End full period */

        /**three month period */
        $beginThreeMonth = new \DateTime(date('Y-m-01', strtotime($values['e_date'] . '-2 months')));
        $endThreeMonth = new \DateTime(date('Y-m-t', strtotime($values['e_date'])));
        $intervalNew = \DateInterval::createFromDateString('1 month');
        $periodNew = new \DatePeriod($beginThreeMonth, $intervalNew, $endThreeMonth);

        /**end three month period */

        $results->transform(function ($val) use ($period, $queryCus, $periodNew) {
            $return['pro_code'] = $val->product_code;
            $return['pro_name'] = $val->product_name;

            foreach ($period as $key => $dt) {
                $count = InvoiceLine::where('product_id', $val->product_id)
                    ->whereMonth('invoice_date', $dt->format('m'))
                    ->whereYear('invoice_date', $dt->format('Y'))
                    ->whereIn('chemist_id', $queryCus->pluck('chemist_id'))
                    ->count(DB::raw('DISTINCT(chemist_id)'));
                $return['month_' . $dt->format('m')] = number_format($count);
            }
            $salable_month_count = 0;
            $salable_month_qty = 0;
            foreach ($periodNew as $key_new => $dt_new) {
                $count_new = InvoiceLine::where('product_id', $val->product_id)
                    ->whereMonth('invoice_date', $dt_new->format('m'))
                    ->whereYear('invoice_date', $dt_new->format('Y'))
                    ->whereIn('chemist_id', $queryCus->pluck('chemist_id'))
                    ->count(DB::raw('DISTINCT(chemist_id)'));

                if ($count_new > 0) {
                    $salable_month_count++;
                    $salable_month_qty += $count_new;
                }
                if ($salable_month_count == 0) {
                    $salable_month_count = 1;
                }
                $return['salable_month_count'] = $salable_month_count;
                $return['last_count'] = number_format($salable_month_qty / $salable_month_count, 2);
                $return['last_count_new'] = $salable_month_qty / $salable_month_count;
            }

            return $return;
        });

        $row = [];

        $row = [
            'special' => true,
            'pro_code' => 'Total',
            'pro_name' => null,
            //    'month_' =>NULL,
            'last_count' => number_format($results->sum('last_count_new'), 2),
        ];
        foreach ($period as $key => $dt) {
            // $count = InvoiceLine::where('product_id',$val->product_id)->whereMonth('invoice_date',$dt->format('m'))->count(DB::raw('DISTINCT(chemist_id)'));
            $row['month_' . $dt->format('m')] = number_format($results->sum('month_' . $dt->format('m')));
        }

        $results->push($row);

        $results = $results->filter(function ($value, $key) {
            return $value['last_count'] > 0;
        })->values();

        $data = $results->values();

        return [
            'results' => $data,
            'count' => $count,
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $values = $request->input('values', []);

        $columnController->text('pro_code')->setLabel('Product Code');
        $columnController->text('pro_name')->setLabel('Description');

        if (isset($values['s_date']) && isset($values['e_date'])) {

            $begin = new \DateTime(date('Y-m-01', strtotime($values['s_date'])));
            $end = new \DateTime(date('Y-m-t', strtotime($values['e_date'])));

            $interval = \DateInterval::createFromDateString('1 month');
            $period = new \DatePeriod($begin, $interval, $end);

            foreach ($period as $key => $month) {
                $columnController->number('month_' . $month->format('m'))->setLabel($month->format('M'));
            }
        }

        $columnController->number('last_count')->setLabel('Last 3 month Avarage');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('chemist')->setLabel('Chemist')->setLink('chemist');
        $inputController->ajax_dropdown('user')->setLabel('User')->setWhere(['u_tp_id' => 10])->setLink('user');
        $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product')->setValidations('');
        $inputController->date('s_date')->setLabel('From')->setLink('s_date');
        $inputController->date('e_date')->setLabel('To')->setLink('e_date');

        $inputController->setStructure([
            ['user', 'chemist', 'pro_id'], ['s_date', 'e_date'],
        ]);
    }
}
