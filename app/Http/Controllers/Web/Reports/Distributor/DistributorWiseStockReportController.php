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

class DistributorWiseStockReportController extends ReportController{

    protected $title = "Distributor Wise Stock Report";
    protected $updateColumnsOnSearch = true;

    public function search( Request $request){
        $values = $request->input('values');
        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            default:
                $sortBy = 'product_code';
                break;
        }

        $stockQuery = DB::table('distributor_stock as ds')
            ->select(
                'p.product_id',
                'p.product_code',
                'p.product_name',
                'ds.db_id',
                'db.db_code as batch_no',
                'db.db_price as price',
                'u.id as dis_id',
                'u.name as db_name',
                'pp.principal_id',
                'pp.principal_name as principal',
                'db.db_expire as exp_date',
                'ds.created_at as date',
                DB::raw('SUM(0) as non_salable_qty'),
                DB::raw('SUM(ds.ds_credit_qty - ds.ds_debit_qty) as salable_qty'),
                DB::raw('SUM(ds.ds_credit_qty - ds.ds_debit_qty) as total_qty'),
                DB::raw('SUM(ds.ds_credit_qty - ds.ds_debit_qty) * db.db_price as total_amt')
            )
            ->join('product as p','p.product_id','ds.product_id')
            ->join('principal as pp','pp.principal_id','p.principal_id')
            ->join('users as u','u.id','ds.dis_id')
            ->join('distributor_batches as db','db.db_id','ds.db_id')
            ->whereNull('ds.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('pp.deleted_at')
            ->groupBy('ds.dis_id','pp.principal_id','ds.product_id','db.db_id');

        if(isset($values['dis_id'])){
            $stockQuery->where('ds.dis_id',$values['dis_id']['value']);
        }

        if(isset($values['principal'])){
            $stockQuery->where('pp.principal_id',$values['principal']['value']);
        }

        if(isset($values['product'])){
            $stockQuery->where('p.product_id',$values['product']['value']);
        }

        // if (isset($values['s_date']) && isset($values['e_date'])) {
            // $stockQuery->whereBetween(DB::raw('DATE(ds.created_at)'), [date('Y-m-d', strtotime($values['s_date'])), date('Y-m-d', strtotime($values['e_date']))]);
        // }

        if(isset($values['e_date'])){
            $stockQuery->whereDate(DB::raw('DATE(ds.created_at)'),'<=',date('Y-m-d', strtotime($values['e_date'])));
        }

        $query = Product::with('principal');

        if(isset($values['product'])){
            $query->where('product_id',$values['product']['value']);
        }

        if(isset($values['principal'])){
            $query->where('principal_id',$values['principal']['value']);
        }

        $count = $this->paginateAndCount($query, $request, $sortBy);

        $stock = $stockQuery->get();
        $products = $query->get();

        $products->transform(function ($val) use ($stock,$values) {
            $query = User::where('u_tp_id', 14);
            if(isset($values['dis_id'])){
                $query->where('id',$values['dis_id']['value']);
            }
            // $query->where('id', '!=', 585);
            $users = $query->get();

            $return['pri_name'] = $val->principal ? $val->principal->principal_name : '';
            $return['pro_code'] = $val->product_code;
            $return['pro_name'] = $val->product_name;

            $total = 0;
            foreach ($users as $key => $row) {
                $stockQty = $stock->where('product_id', $val->product_id)->where('dis_id', $row->id)->sum('total_qty');
                $return['dis_' . $row->id] = number_format($stockQty, 2);
                $total += $stockQty;
            }

            $return['stock_qty'] = isset($total) ? number_format($total, 2) : 0;

            return $return;
        });

        $row = [];

        $row['special'] = true;
        $row['pri_name'] = '';

        $products->push($row);

        return [
            'results' => $products,
            'values' => $values,
            'count' => $count
        ];


    }

    public function setColumns(ColumnController $columnController, Request $request){
        $values = $request->values;
        $query = User::where('u_tp_id', 14);
        // $query->where('id', '!=', 585);

        if (isset($values['dis_id'])) {
            $query->where('id', $values['dis_id']['value']);
        }

        $users = $query->get();

        $columnController->text('pri_name')->setLabel('Principal');
        $columnController->text('pro_code')->setLabel('Product Code');
        $columnController->text('pro_name')->setLabel('Product');

        foreach ($users as $key => $user) {
            $columnController->text('dis_' . $user->id)->setLabel($user->name);
        }

        $columnController->number('stock_qty')->setLabel('Total');

    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_type')])->setValidations('');
        $inputController->ajax_dropdown('principal')->setLabel('Principal')->setLink('principal')->setValidations('');
        $inputController->ajax_dropdown('product')->setLabel('Product')->setLink('product')->setValidations('');

        // $inputController->date('s_date')->setLabel('From');
        $inputController->date('e_date')->setLabel('Date');

        $inputController->setStructure([
            ['dis_id','e_date'],
            ['principal','product'],
            // // ['s_date','e_date']
            // ['e_date']
        ]);
    }

}
