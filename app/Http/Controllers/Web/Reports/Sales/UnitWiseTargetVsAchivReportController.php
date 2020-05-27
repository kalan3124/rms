<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use App\Models\Product;
use App\Models\SalesmanValidCustomer;
use App\Models\SalesmanValidPart;
use App\Models\SfaTarget;
use App\Models\SfaTargetProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitWiseTargetVsAchivReportController extends ReportController
{

    protected $title = "Unit wise Target vs Achivement  Report";

    public function search($request)
    {
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            case 'pro_code':
                $sortBy = 'product_id';
                break;
            case 'pro_name':
                $sortBy = 'product_name';
                break;
            default:
                $sortBy = 'product_id';
                break;
        }

        $invoice = DB::table('product as p')
            ->join('invoice_line as il', 'il.product_id', 'p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'il.product_id',
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('il.product_id');



        $return = DB::table('product as p')
            ->join('return_lines as il', 'il.product_id', 'p.product_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'il.product_id',
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS return_qty'),
                DB::raw('SUM(IFNULL(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
            ])
            ->whereNull('il.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pi.deleted_at')
            ->groupBy('il.product_id');

        if (isset($values['user'])) {
            $products = SalesmanValidPart::where('u_id', $values['user']['value'])->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['s_date'])))->get();
            $chemists = SalesmanValidCustomer::where('u_id', $values['user']['value'])->whereDate('from_date', '<=', date('Y-m-01', strtotime($values['s_date'])))->whereDate('to_date', '>=', date('Y-m-t', strtotime($values['s_date'])))->get();

            $invoice->whereIn('il.product_id', $products->pluck('product_id')->all());
            $invoice->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

            $return->whereIn('il.product_id', $products->pluck('product_id')->all());
            $return->whereIn('il.chemist_id', $chemists->pluck('chemist_id')->all());

            $query = SfaTarget::query();
            $query->where('u_id', $values['user']['value']);
            $query->where('trg_year', date('Y', strtotime($values['s_date'])));
            $query->where('trg_month', date('m', strtotime($values['s_date'])));
            $query->latest();
            $target_user = $query->first();

            $target_user = SfaTargetProduct::where('sfa_trg_id', $target_user['sfa_trg_id'])->get();

            $target_user->transform(function ($val) {
                return [
                    'product_id' => $val->product_id,
                    'stp_qty' => $val->stp_qty,
                ];
            });

        } else {
            $query = SfaTarget::query();
            $query->where('trg_year', date('Y', strtotime($values['s_date'])));
            $query->where('trg_month', date('m', strtotime($values['s_date'])));
            $target_user = $query->get();

            $target_user = SfaTargetProduct::whereIn('sfa_trg_id', $target_user->pluck('sfa_trg_id')->all())->get();

            $target_user->transform(function ($val) {
                return [
                    'product_id' => $val->product_id,
                    'stp_qty' => $val->stp_qty,
                ];
            });
        }

        // $grandproduct_achive=0;
        if (isset($values['pro_id'])) {
            $invoice->where('il.product_id', $values['pro_id']['value']);
            $return->where('il.product_id', $values['pro_id']['value']);
        }

        if (isset($values['s_date'])) {
            $invoice->whereDate('il.invoice_date', '>=', date('Y-m-01', strtotime($values['s_date'])));
            $invoice->whereDate('il.invoice_date', '<=', date('Y-m-t', strtotime($values['s_date'])));

            $return->whereDate('il.invoice_date', '>=', date('Y-m-01', strtotime($values['s_date'])));
            $return->whereDate('il.invoice_date', '<=', date('Y-m-t', strtotime($values['s_date'])));
        }

        $grandproductnet = DB::table(DB::raw("({$invoice->toSql()}) as sub"))
            ->mergeBindings(get_class($invoice) == 'Illuminate\Database\Eloquent\Builder' ? $invoice->getQuery() : $invoice)->sum(DB::raw('net_qty'));

        $grandproductnet2 = DB::table(DB::raw("({$return->toSql()}) as sub"))
            ->mergeBindings(get_class($return) == 'Illuminate\Database\Eloquent\Builder' ? $return->getQuery() : $return)->sum(DB::raw('return_qty'));

        $grandproduct_achive=$grandproductnet-$grandproductnet2;

        $invoices = $invoice->get();
        $returns = $return->get();

        $allachivements = $invoices->concat($returns);

        $product = Product::whereIn('product_id', $allachivements->pluck('product_id')->all());
        $count = $this->paginateAndCount($product, $request, $sortBy);

        $results = $product->get();

        $productAchi = 0;
        $balance = 0;
        $achi_ = 0;
        $results->transform(function ($val) use ($invoices, $returns, $target_user, $productAchi, $balance, $achi_) {

            if ($target_user) {
                $target_pro = $target_user->where('product_id', $val->product_id)->sum('stp_qty');
            }

            $salesAchi = $invoices->where('product_id', $val->product_id)->sum('net_qty');
            $returnAchi = $returns->where('product_id', $val->product_id)->sum('return_qty');

            if (isset($salesAchi) && isset($returnAchi)) {
                $productAchi = $salesAchi - $returnAchi;
            }

            if ((isset($target_pro) && isset($productAchi) && ($target_pro > 0 && $productAchi > 0))) {
                $balance = $target_pro - $productAchi;
            }

            if ((isset($target_pro) && isset($productAchi) && ($target_pro > 0 && $productAchi > 0))) {
                $achi_ = $productAchi / $target_pro * 100;
            }

            return [
                'pro_code' => $val->product_code,
                'pro_code_style' => $achi_ >= 100 ? [
                    'background' => '#fab2ac',
                    'border' => '1px solid #fff',
                ] : null,
                'pro_name' => $val->product_name,
                'pro_name_style' => $achi_ >= 100 ? [
                    'background' => '#fab2ac',
                    'border' => '1px solid #fff',
                ] : null,
                'target' => isset($target_pro) ? $target_pro : 0,
                'target_style' => $achi_ >= 100 ? [
                    'background' => '#fab2ac',
                    'border' => '1px solid #fff',
                ] : null,
                'target_new' => isset($target_pro) ? $target_pro : 0,
                'achi' => $productAchi ? round($productAchi, 2) : 0,
                'achi_style' => $achi_ >= 100 ? [
                    'background' => '#fab2ac',
                    'border' => '1px solid #fff',
                ] : null,
                'achi_new' => $productAchi ? $productAchi : 0,
                'ach' => $achi_ ? number_format($achi_, 2) : '0.00',
                'ach_style' => $achi_ >= 100 ? [
                    'background' => '#fab2ac',
                    'border' => '1px solid #fff',
                ] : null,
                'balance' => $balance ? number_format($balance, 2) : '0.00',
                'balance_style' => $achi_ >= 100 ? [
                    'background' => '#fab2ac',
                    'border' => '1px solid #fff',
                ] : null,
                'balance_new' => $balance ? $balance : 0,
            ];
        });

        $row=[];

        $row = [
            'special' => true,
            'pro_code' => 'Total',
            'pro_name' => null,
            'target' => number_format($results->sum('target_new')),
            'achi' => number_format($results->sum('achi_new')),
            'ach_%' => null,
            //    'ach_%' => number_format($results->sum('ach_%'),2),
            'balance' => number_format($results->sum('balance_new'), 2),
        ];

        $newrow=[];

        $newrow=[
            'special' => true,
            'pro_code' => 'Grand Total',
            'pro_name' => null,
            'target' => null,
            'achi' => number_format($grandproduct_achive),
            'ach_%' => null,
            //    'ach_%' => number_format($results->sum('ach_%'),2),
            'balance' => null,
        ];

        $results->push($row);
        $results->push($newrow);

        return [
            'results' => $results,
            'count' => $count,
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request)
    {
        $columnController->text('pro_code')->setLabel('Product Code IFS');
        $columnController->text('pro_name')->setLabel('Product');
        $columnController->number('target')->setLabel('Target');

        $columnController->number('achi')->setLabel('Achivement');
        $columnController->number('ach')->setLabel('%');
        $columnController->number('balance')->setLabel('Balance Value');
    }

    public function setInputs($inputController)
    {
        $inputController->ajax_dropdown('user')->setLabel('User')->setLink('user')->setWhere(['u_tp_id' => config('shl.sales_rep_type')])->setValidations('');
        $inputController->ajax_dropdown('pro_id')->setLabel('Product')->setLink('product')->setValidations('');
        $inputController->date('s_date')->setLabel('Month');
        $inputController->date('e_date')->setLabel('To');

        $inputController->setStructure([
            ['user', 'pro_id', 's_date'],
        ]);
    }
}
