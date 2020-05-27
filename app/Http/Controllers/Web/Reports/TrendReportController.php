<?php
namespace App\Http\Controllers\Web\Reports;

use App\Traits\Territory;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\WebAPIException;
use App\Models\TeamUser;
use App\Models\InvoiceLine;
use App\Models\TeamMemberPercentage;
use App\Models\TeamProduct;
use App\Models\UserTeam;

class TrendReportController extends ReportController {
    use Territory;

    protected $title = "Trend Report";

    protected $defaultSortColumn="sub_twn_code";

    protected $updateColumnsOnSearch = true;

    public function search(Request $request){

        $validation = Validator::make($request->all(),[
            'values'=>'required|array',
            'values.u_id'=>'required|array',
            'values.u_id.value'=>'required',
            'values.s_date'=>'required|date',
            'values.e_date'=>'required|date'
        ]);

        if($validation->fails()){
            throw new WebAPIException("User field and date fields are required");
        }

        $userId = $request->input('values.u_id.value');
        $user = User::find($userId);
        $products = Product::getByUserForSales($user);

        $teamUsers = TeamUser::where('u_id',$userId)->latest()->first();
        $teamId = $teamUsers->tm_id;

        $subTownId = $request->input('values.sub_twn_id.value');
        $startDate = $request->input('values.s_date');
        $endDate = $request->input('values.e_date');

        $query = InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$userId)
            ->groupBy(['st.sub_twn_id','p.product_id'])
            ->select([
                'st.sub_twn_id',
                'p.product_id',
                InvoiceLine::salesAmountColumn('amount'),
                InvoiceLine::salesQtyColumn('qty')
                ])
            ->join('product AS p','p.product_id','il.product_id')
            ->join('chemist AS c','il.chemist_id','c.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->whereNull('il.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('st.deleted_at');

        $queryReturns = InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'),$userId,true)
            ->groupBy(['st.sub_twn_id','p.product_id'])
            ->select([
                'st.sub_twn_id',
                'p.product_id',
                InvoiceLine::salesAmountColumn('amount','pi',true),
                InvoiceLine::salesQtyColumn('qty','pi',true)

            ])
            ->join('product AS p','p.product_id','rl.product_id')
            ->join('chemist AS c','rl.chemist_id','c.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->whereNull('rl.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('st.deleted_at');

        /** @var \App\Models\User $user */

        if(in_array($user->getRoll(),[
            config('shl.product_specialist_type'),
            config('shl.medical_rep_type'),
            config('shl.field_manager_type')
        ])){
            $query->whereIn('p.product_id',$products->pluck('product_id')->all());
            $queryReturns->whereIn('p.product_id',$products->pluck('product_id')->all());
        }


        $teams = UserTeam::where('u_id',$user->getKey())->get();
        if($teams->count()){
            $products = TeamProduct::whereIn('tm_id',$teams->pluck('tm_id')->all())->get();
            $query->whereIn('p.product_id',$products->pluck('product_id')->all());
            $queryReturns->whereIn('p.product_id',$products->pluck('product_id')->all());
        }

        $subTowns = $this->getAllocatedTerritories($user);

        InvoiceLine::whereWithSalesAllocation($query,'st.sub_twn_id',$subTowns->pluck('sub_twn_id')->all());
        InvoiceLine::whereWithSalesAllocation($queryReturns,'st.sub_twn_id',$subTowns->pluck('sub_twn_id')->all(),true);

        $subTowns = $this->getAllocatedTerritoriesForSales($user);

        $subTowns->sortBy('sub_twn_name');
        $subTowns->values()->all();

        if($subTownId){
            $query->where('st.sub_twn_id',$subTownId);
            $queryReturns->where('st.sub_twn_id',$subTownId);
        }

        if($startDate&&$endDate){
            $query->whereDate('il.invoice_date','>=',$startDate);
            $query->whereDate('il.invoice_date','<=',$endDate);

            $queryReturns->whereDate('rl.invoice_date','>=',$startDate);
            $queryReturns->whereDate('rl.invoice_date','<=',$endDate);
        }

        $result = $query->get();
        $returns = $queryReturns->get();

        $rows = [];
        $finishedRow = [
            'sub_twn_name'=>"Grand Total",
            "sub_twn_code"=>"",
            'grnd_val'=>0,
            'grnd_qty'=>0,
            'special'=>1
        ];

        // return $subTowns;
        foreach ($subTowns as $subTown) {
            $subTown = (object) $subTown;

            $row =[
                'sub_twn_name'=>isset($subTown->sub_twn_name)?$subTown->sub_twn_name:"",
                'sub_twn_code'=>isset($subTown->sub_twn_code)?$subTown->sub_twn_code:"",
            ];

            $amountTotal = 0;
            $qtyTotal = 0;

            foreach($products as $product){
                $amount = $result->where('product_id',$product->product_id)->where('sub_twn_id',$subTown->sub_twn_id)->first();
                $returnAmount = $returns->where('product_id',$product->product_id)->where('sub_twn_id',$subTown->sub_twn_id)->first();

                if( !isset( $finishedRow['product_'.$product->product_id])){
                    $finishedRow['product_'.$product->product_id] = 0;
                }

                if($amount||$returnAmount){
                    $qty =  ($amount?$amount->qty:0)-($returnAmount?$returnAmount->qty:0);
                    $row['product_'.$product->product_id] = $qty;
                    $finishedRow['product_'.$product->product_id] +=$qty;
                    $amountTotal += ($amount?$amount->amount:0)-($returnAmount?$returnAmount->amount:0);
                    $qtyTotal += $qty;
                } else {
                    $row['product_'.$product->product_id] = 0.00;
                }
            }

            $row['grnd_val'] = number_format($amountTotal,2);
            $row['grnd_qty'] = $qtyTotal;

            $finishedRow['grnd_val'] += $amountTotal;
            $finishedRow['grnd_qty'] += $qtyTotal;

            $rows[] = $row;
        }

        $rows[] = $finishedRow;

        return [
            'count'=>0,
            'results'=>$rows
        ];
    }

    public function setColumns($columnController, Request $request){
        $columnController->text('sub_twn_name')->setLabel('Sub Town Name');
        $columnController->text('sub_twn_code')->setLabel('Sub Town Code');

        $userId = $request->input('values.u_id.value');

        if($userId){
            $user = User::find($userId);

            $products = Product::getByUserForSales($user);

            foreach($products as $product){
                $columnController->text('product_'.$product->product_id)->setLabel($product->product_name);
            }
        }

        $columnController->number('grnd_val')->setLabel('Grand Total');
        $columnController->number('grnd_qty')->setLabel("Grand Qty");

    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('tm_id')->setLabel("Team")->setLink('team')->setWhere([
            'divi_id'=>'{divi_id}'
        ])->setValidations('');
        $inputController->ajax_dropdown('divi_id')->setLabel("Division")->setLink('division')->setValidations('');
        $inputController->ajax_dropdown('u_id')->setLabel('User')->setLink('user')->setWhere([
            'tm_id'=>'{tm_id}',
            'divi_id'=>'{divi_id}',
            'u_tp_id'=>'3'.'|'.config('shl.product_specialist_type')
        ]);
        $inputController->ajax_dropdown('sub_twn_id')->setLabel("Sub Town")->setLink('sub_town')->setValidations('');

        $inputController->date('s_date')->setLabel("Start Date");
        $inputController->date('e_date')->setLabel("End Date");

        $inputController->setStructure([
            ['divi_id','tm_id','u_id'],
            ['sub_twn_id','s_date','e_date']
        ]);
    }
}
