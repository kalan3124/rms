<?php
namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Http\Controllers\Web\Reports\ReportController;

use Illuminate\Http\Request;
use App\Form\Columns\ColumnController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\DistributorStock;
use App\Models\Principal;
use App\Models\Product;
use App\Models\User;

class ListCreditReportController extends ReportController{

    protected $title = "List Credit Report";
    protected $updateColumnsOnSearch = true;

    public function search( Request $request){
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            default:
                $sortBy = 'return_number';
                break;
        }

        $returnQuery = DB::table('distributor_return as dir')
            ->select([
                'dir.dis_return_id',
                DB::raw('DATE(dir.return_date) as return_date'),
                // 'dir.return_date',
                'dir.dsr_id',
                'dir.discount',
                'dir.dist_return_number as return_number',
                'di.di_number as invoice_number',
                'dc.dc_code as customer_code',
                'dc.dc_name as customer_name',
                'dir.dis_id',
                DB::raw('SUM(dil.dri_price * dil.dri_qty) as sale_return_amount'),
                DB::raw('SUM(dil.unit_price_no_tax * dil.dri_qty) as sale_return_amount_wt')
            ])
            ->leftJoin('distributor_invoice as di', 'dir.di_id', 'di.di_id')
            ->join('distributor_customer as dc', 'dc.dc_id', 'dir.dc_id')
            ->join('distributor_return_item as dil', 'dil.dis_return_id', 'dir.dis_return_id')
            ->whereNull('dir.deleted_at')
            ->whereNull('di.deleted_at')
            ->whereNull('dc.deleted_at')
            ->whereNull('dil.deleted_at')
            ->groupBy('dir.dis_return_id')
            ->orderBy('return_date');

        // return $returnQuery->get();

        if (isset($values['s_date']) && isset($values['e_date'])) {
            $returnQuery->whereBetween( DB::raw( 'DATE(dir.created_at)'),[ date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        }

        if (isset($values['dc_id'])) {
            $returnQuery->where('dc.dc_id', $values['dc_id']['value']);
        }

        if (isset($values['distributor'])) {
            $returnQuery->where('dir.dis_id', $values['distributor']['value']);
        }

        $user = Auth::user();

        if ($user->u_tp_id == config('shl.distributor_type')) {
            $returnQuery->where('dir.dis_id', $user->getKey());
        }

        $count = $this->paginateAndCount($returnQuery, $request,'return_date');

        $results = $returnQuery->get();


        $begin = new \DateTime(date('Y-m-d', strtotime($values['s_date'])));
        $end = new \DateTime(date('Y-m-d', strtotime($values['e_date'])));

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);

        $result = [];
        $gt_goods_value = 0;
        $gt_crn_value = 0;

        $a=true;
        $b=true;
        foreach ($period as $key => $day) {
            $row_amount_crn = 0;
            $row_amount_g_val = 0;
            foreach ($results as $key => $val) {

                if($val->return_date == $day->format('Y-m-d')){
                    if($a){
                        $result[] = [
                            'crn_no'=>$day->format('Y-m-d'),
                            'invoice_no'=>null,
                            'customer_code'=>null,
                            'customer_name'=>null,
                            'goods_value'=>null,
                            'header_dis'=>null,
                            'line_dis'=>null,
                            'tax'=>null,
                            'crn_value'=>null,
                            'special'=>true
                        ];
                    }

                    $a=false;
                    $result[] = [
                        'crn_no'=>$val->return_number,
                        'invoice_no'=>$val->invoice_number,
                        'customer_code'=>$val->customer_code,
                        'customer_name'=>$val->customer_name,
                        'goods_value'=>number_format($val->sale_return_amount_wt,2),
                        'header_dis'=>number_format($val->discount,2),
                        'line_dis'=>number_format($val->discount,2),
                        'tax'=>number_format(($val->sale_return_amount-$val->sale_return_amount_wt),2),
                        'crn_value'=>number_format($val->sale_return_amount,2)
                    ];
                    $row_amount_g_val += $val->sale_return_amount_wt;
                    $row_amount_crn += $val->sale_return_amount;
                }
                else{
                    $a=true;
                }
            }

            if($row_amount_crn>0){
                $gt_goods_value += $row_amount_g_val;
                $gt_crn_value += $row_amount_crn;

                $result[] = [
                    'crn_no'=>'Total for day',
                    'invoice_no'=>null,
                    'customer_code'=>null,
                    'customer_name'=>null,
                    'goods_value'=>number_format($row_amount_g_val,2),
                    'header_dis'=>null,
                    'line_dis'=>null,
                    'tax'=>null,
                    'crn_value'=>number_format($row_amount_crn,2),
                    'special'=>true
                ];
            }

        }

        $result[] = [
            'crn_no'=>'Total',
            'invoice_no'=>null,
            'customer_code'=>null,
            'customer_name'=>null,
            'goods_value'=>number_format($gt_goods_value,2),
            'goods_value_'=>null,
            'header_dis'=>null,
            'line_dis'=>null,
            'tax'=>null,
            'crn_value'=>number_format($gt_crn_value,2),
            'crn_value_'=>null,
            'special'=>true
        ];


        return [
            'results' => $result,
            'count' => $count
        ];
    }


    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('crn_no')->setLabel('CRN No');
        $columnController->text('invoice_no')->setLabel('Invoice No');
        $columnController->text('customer_code')->setLabel('Customer Code');
        $columnController->text('customer_name')->setLabel('Customer Name');
        $columnController->number('goods_value')->setLabel('Goods Value');
        $columnController->number('header_dis')->setLabel('Header Discount');
        $columnController->number('line_dis')->setLabel('Line Discount');
        // $columnController->number('special_dis')->setLabel('Special Discount');
        $columnController->number('tax')->setLabel('Tax');
        $columnController->number('crn_value')->setLabel('CRN Value');
    }

    public function setInputs($inputController){
        // $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_type')])->setValidations('');
        // $inputController->ajax_dropdown('principal')->setLabel('Principal')->setLink('principal')->setValidations('');
        // $inputController->ajax_dropdown('product')->setLabel('Product')->setLink('product')->setValidations('');

        $inputController->ajax_dropdown('dc_id')->setLabel('Distributor Customer')->setLink('distributor_customer')->setValidations('');
        $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('Date');

        $inputController->setStructure([
            ['dc_id'],
            ['s_date','e_date']
        ]);
    }

}
