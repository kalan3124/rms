<?php
namespace App\Http\Controllers\Web\Reports\Distributor;

use App\Http\Controllers\Web\Reports\ReportController;

use Illuminate\Http\Request;
use App\Form\Columns\ColumnController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class StockStatementReportController extends ReportController{

    protected $title = "Stock Statement Report";

    public function search( Request $request){

        $values = $request->input('values');

        $sortBy = $request->input('sortBy');

        switch ($sortBy) {
            default:
                $sortBy = 'u.name';
                break;
        }

        $query = DB::table('distributor_stock as ds')
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
            $query->where('u.id',$values['dis_id']['value']);
        }

        if(isset($values['principal'])){
            $query->where('pp.principal_id',$values['principal']['value']);
        }

        if(isset($values['product'])){
            $query->where('p.product_id',$values['product']['value']);
        }

        if(isset($values['distributor_batch'])){
            $query->where('db.db_id',$values['distributor_batch']['value']);
        }

        if(isset($values['date'])){
            $query->whereDate('ds.created_at','<=',$values['date']);
        }

        $user = Auth::user();

          if($user->u_tp_id == config('shl.distributor_type')){
              $query->where('u.id',$user->getKey());
          }


        $results = $query->get();

        $results->transform(function($row){return (array) $row;});

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $newRow  = [
            'special' => true,
            'db_name'=> 'Total',
            'salable_qty' => $results->sum('salable_qty'),
            'total_qty' => $results->sum('total_qty'),
            'total_amt' => $results->sum('total_amt')
        ];

        $results->push($newRow);

        return [
            'results'=>$results->toArray(),
            'count'=>$count
        ];
    }

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('db_name')->setLabel('Distributor');
        $columnController->text('principal')->setLabel('Principal');
        $columnController->text('product_code')->setLabel('Product Code');
        $columnController->text('product_name')->setLabel('Product Name');
        $columnController->text('batch_no')->setLabel('Batch No');
        $columnController->text('price')->setLabel('Price');
        $columnController->text('salable_qty')->setLabel('Salable Qty.');
        $columnController->text('non_salable_qty')->setLabel('Non Salable Qty.');
        $columnController->text('exp_date')->setLabel('Expire Date');
        $columnController->text('total_qty')->setLabel('Total Qty.');
        $columnController->text('total_amt')->setLabel('Total Amount');
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('dis_id')->setLabel('Distributor')->setLink('user')->setWhere(['u_tp_id'=>config('shl.distributor_type')]);
        $inputController->ajax_dropdown('principal')->setLabel('Principal')->setLink('principal');
        $inputController->ajax_dropdown('product')->setLabel('Product')->setLink('product');
        $inputController->ajax_dropdown('distributor_batch')->setLabel('Batch')->setLink('distributor_batch')->setWhere([
            'distributor'=>'{dis_id}',
            'product'=>'{product}'
        ]);
        $inputController->date('date')->setLabel('Date');

        $inputController->setStructure([
            ['dis_id','date'],
            ['principal','product','distributor_batch']
        ]);
    }

}
