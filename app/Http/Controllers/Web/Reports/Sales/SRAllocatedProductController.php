<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Http\Controllers\Web\Reports\ReportController;
use App\Form\Columns\ColumnController;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SRAllocatedProductController extends ReportController{

    protected $title = "SR Allocated Products";

    public function search($request){

        $user = Auth::user();

        if($user){
            $userCode = substr($user->u_code,0,4);

            $area = Area::where('ar_code',$userCode)->first();
        }

        $query = DB::table('salesman_valid_parts AS svp')
        ->join('product AS p','p.product_id','svp.product_id')
        ->join('users AS u','u.id','svp.u_id','u.u_code')
        ->select([
            'svp.salesman_code',
            'u.name',
            'p.product_code',
            'p.product_name',
            'svp.contract',
            'svp.from_date',
            'svp.to_date'
        ])
        ->whereNull('svp.deleted_at')
        ->whereNull('p.deleted_at')
        ->whereNull('u.deleted_at');

        if($request->has('values.user.value')){
            $query->where('u.id',$request->input('values.user.value'));
        }

        if($request->has('values.product.value')){
            $query->where('p.product_id',$request->input('values.product.value'));
        }

        if(!$request->has('values.user.value') && !$request->has('values.product.value')){
            if(isset($area->ar_code)){
                $query->where('u.u_code','LIKE','%'.$area->ar_code.'%');
            }
        }

        $sortMode = $request->input('sortMode')??'desc';
        $sortBy = 'svp.salesman_code';

        switch ($request->input('sortBy')) {
            case 'user_name':
                $sortBy='u.name';
                break;
            case 'chemist_code':
                $sortBy='p.product_code';
                break;
            case 'product_name':
                $sortBy='p.product_name';
                break;
            case 'contract':
                $sortBy='svp.contract';
                break;
            case 'from_date':
                $sortBy='svp.from_date';
                break;
            case 'to_date':
                $sortBy='svp.to_date';
                break;
            default:
                break;
        }

        $query->orderBy($sortBy,$sortMode);

        $count = $this->paginateAndCount($query,$request,$sortBy);

        $result = $query->get();


        $formatedResults = [];

        $u_code_num = "";

        foreach ($result as $key => $value) {

            $row = [];
            $counts = $result->where('salesman_code', $value->salesman_code)->count();

            if ($u_code_num != $value->salesman_code) {
                $row['user_code'] = $value->salesman_code;
                $row['user_code_rowspan'] = $counts;
                $row['user_name'] = $value->name;
                $row['user_name_rowspan'] = $counts;
                $row['contract'] = $value->contract;
                $row['contract_rowspan'] = $counts;
                $row['from_date'] = $value->from_date;
                $row['from_date_rowspan'] = $counts;
                $row['to_date'] = $value->to_date;
                $row['to_date_rowspan'] = $counts;
            } else {
                $row['user_code'] = null;
                $row['user_code_rowspan'] = 0;
                $row['user_name'] = null;
                $row['user_name_rowspan'] = 0;
                $row['contract'] = null;
                $row['contract_rowspan'] = 0;
                $row['from_date'] = null;
                $row['from_date_rowspan'] = 0;
                $row['to_date'] = null;
                $row['to_date_rowspan'] = 0;

            }

            $row['user_code'] = $value->salesman_code;
            $row['user_name'] = $value->name;
            $row['product_code'] = $value->product_code;
            $row['product_name'] = $value->product_name;
            $row['contract'] = $value->contract;
            $row['from_date'] = $value->from_date;
            $row['to_date'] = $value->to_date;


            $u_code_num = $value->salesman_code;

            $formatedResults[] = $row;
        }

        $result = $formatedResults;


        // $result->transform(function($row){
        //     return [
        //         'user_code'=>$row->salesman_code,
        //         'user_name'=>$row->name,
        //         'product_code'=>$row->product_code,
        //         'product_name'=>$row->product_name,
        //         'contract'=>$row->contract,
        //         'from_date'=>$row->from_date,
        //         'to_date'=>$row->to_date,
        //     ];
        // });

        return [
            'results'=>$result,
            'count'=>$count
        ];

    }

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text('user_code')->setLabel('SR Code');
        $columnController->text('user_name')->setLabel('SR Name');
        $columnController->text('product_code')->setLabel('Product Code');
        $columnController->text('product_name')->setLabel('Product Name');
        $columnController->text('contract')->setLabel('DC Center');
        $columnController->text("from_date")->setLabel("From Date");
        $columnController->text("to_date")->setLabel("To Date");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('user')->setWhere(['u_tp_id'=>10])->setLabel("user")->setLink('user');
        $inputController->ajax_dropdown('product')->setLabel("Product")->setLink('product');
        $inputController->setStructure(['user','product']);
    }

}
