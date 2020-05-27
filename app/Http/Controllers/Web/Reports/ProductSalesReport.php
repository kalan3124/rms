<?php
namespace App\Http\Controllers\Web\Reports;

use App\Models\InvoiceLine;
use Illuminate\Http\Request;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Auth;

use App\Traits\Team;
use App\Traits\Territory;

use App\Models\User;
use App\Models\SubTown;
use App\Models\Team as ModelsTeam;
use App\Models\TeamUser;
use App\Models\Town;
use App\Exceptions\WebAPIException;

class ProductSalesReport extends ReportController
{
    use Team,Territory;

    protected $title = "Product Wise Sales Report";

    protected $defaultSortColumn="product_code";

    public function search(Request $request){

        $user = null;
        $teamUser = null;
        $team = null;
        if($request->has('values.user.value')){
            //filter by mr products
            $user = User::find($request->input('values.user.value'));
            $teamUsers = TeamUser::where('u_id',$user->getKey())->latest()->first();
            $teamUser = isset($teamUsers->u_id)?$teamUsers->u_id:0;
        }

        $user = User::find($request->input('values.user.value'));

        if($request->has('values.user.value') && $user->getRoll() == config("shl.field_manager_type")){
            // $team = ModelsTeam::where('fm_id',$user->id)->first();
            $teamUser = $user->id;
        }

        $query = InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$teamUser?$teamUser:0)
            ->join('product AS p','il.product_id','=','p.product_id')
            ->join('sub_town AS st','st.sub_twn_code','il.city')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                            })
            ->select([
                'il.identity',
                'il.product_id',
                'p.product_code',
                'p.product_name',
                InvoiceLine::salesAmountColumn('bdgt_value'),
                InvoiceLine::salesQtyColumn('gross_qty'),
                InvoiceLine::salesQtyColumn('net_qty'),
                DB::raw('0 AS return_qty'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ])
            ->groupBy('il.product_id');

            /** */
        $rtnqQuery = InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'),$teamUser?$teamUser:0,true)
            ->join('product AS p','rl.product_id','=','p.product_id')
            ->join('sub_town AS st','st.sub_twn_code','rl.city')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->select([
                'rl.identity',
                'rl.product_id',
                'p.product_code',
                'p.product_name',
                DB::raw('0 AS gross_qty'),
                InvoiceLine::salesAmountColumn('rt_bdgt_value',true),
                InvoiceLine::salesQtyColumn('return_qty',true),
                DB::raw('0 AS net_qty'),
                DB::raw('0 AS bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ])
            ->groupBy('rl.product_id');

            if($request->has('values.ar_id.value')){
                $sub_town = DB::table('sub_town as st')
                ->join('town as t','t.twn_id','st.twn_id')
                ->select('st.sub_twn_code')
                ->where('t.ar_id',$request->input('values.ar_id.value'))
                ->where('st.deleted_at',NULL)
                ->where('t.deleted_at',NULL)
                ->groupBy('st.sub_twn_id')
                ->get();
                $allocatedSubTowns = $sub_town->pluck('sub_twn_code')->all();
                $query->whereIn('il.city',$allocatedSubTowns);
                $rtnqQuery->whereIn('rl.city',$allocatedSubTowns);
            }

            if($request->has('values.sub_twn_id.value')){
                $sub_town = SubTown::where('sub_twn_id',$request->input('values.sub_twn_id.value'))->get();
                $query->where('il.city',$sub_town->pluck('sub_twn_code')->all());
                $rtnqQuery->where('rl.city',$sub_town->pluck('sub_twn_code')->all());
            }

            if($request->has('values.s_date') && $request->has('values.e_date')){
                $query->whereBetween('il.invoice_date',[date('Y-m-d 00:00:00',strtotime($request->input('values.s_date'))),date('Y-m-d 23:59:59',strtotime($request->input('values.e_date')))]);
                $rtnqQuery->whereBetween('rl.invoice_date',[date('Y-m-d 00:00:00',strtotime($request->input('values.s_date'))),date('Y-m-d 23:59:59',strtotime($request->input('values.e_date')))]);
            }

            if($request->has('values.product_id.value')){
                $query->where('il.product_id',$request->input('values.product_id.value'));
                $rtnqQuery->where('rl.product_id',$request->input('values.product_id.value'));
            }

            if($user){
                //filture by mr products
                try {
                    $teamProducts = $this->getProductsByUser($user);
                } catch (\Exception $e) {
                    throw new WebAPIException("Field manager does not in any team");
                }
                // $teamProducts = $this->getProductsByUser($user);
                InvoiceLine::whereWithSalesAllocation($query,'il.product_id',$teamProducts->pluck('product_id')->all());
                $rtnqQuery->whereIn('rl.product_id',$teamProducts->pluck('product_id')->all());
                // //filture by mr allocated areas
                $getAllocatedSubCodes = $this->getAllocatedTerritories($user);
                InvoiceLine::whereWithSalesAllocation($rtnqQuery,'rl.city',$getAllocatedSubCodes->pluck('sub_twn_code')->all(),true);
                InvoiceLine::whereWithSalesAllocation($query,'il.city',$getAllocatedSubCodes->pluck('sub_twn_code')->all());
            }

            /**
             * FOR FM LOGIN FILTURATION
             */
            $loggedUser = Auth::user();
            if($loggedUser->getRoll()==config("shl.field_manager_type")){
                //filture by mr products
                $user = User::find($loggedUser->getKey());
                try {
                    $teamProducts = $this->getProductsByUser($user);
                } catch (\Throwable $th) {
                    throw new WebAPIException("Field manager does not in any team");
                }
                // $teamProducts = $this->getProductsByUser($user);
                $query->whereIn('il.product_id',$teamProducts->pluck('product_id')->all());
                $rtnqQuery->whereIn('rl.product_id',$teamProducts->pluck('product_id')->all());
                // //filture by mr allocated areas
                $getAllocatedSubCodes = $this->getAllocatedTerritories($user);
                $query->whereIn('il.city',$getAllocatedSubCodes->pluck('sub_twn_code')->all());
                $rtnqQuery->whereIn('rl.city',$getAllocatedSubCodes->pluck('sub_twn_code')->all());
            }

            $sortMode = $request->input('sortMode')??'desc';
            $sortBy = 'p.product_code';

            switch ($request->input('sortBy')) {
                case 'product_name':
                    $sortBy='p.product_name';
                    break;
                default:
                    break;
            }

            $query->orderBy($sortBy,$sortMode);

            // $count = $query->count();
            $finalResult = $query->get();
            $finalRtnResult = $rtnqQuery->get();

            $allProducts = $finalResult->merge($finalRtnResult);
            $allProducts->all();

            $allProducts = $allProducts->unique(function ($item) {
                return $item->product_code;
            });
            $allProducts->values()->all();
            $count = 0;

            $results = $allProducts->values();
            $results->all();

            $results->transform(function($row)use($results,$finalRtnResult){
                $grossQty = 0;
                $netQty = 0;
                $rtnQty = 0;
                $netValue = 0;
                foreach ($results AS $inv){
                    if($row->product_code == $inv->product_code){
                        $grossQty += $inv->gross_qty;
                        $netQty += $inv->net_qty;
                        $netValue += $inv->bdgt_value;
                    }
                }
                foreach ($finalRtnResult AS $rtn){
                    if($row->product_code == $rtn->product_code){
                        $rtnQty += $rtn->return_qty;
                        $netQty -= $rtn->return_qty;
                        $netValue -= $rtn->rt_bdgt_value;
                    }
                }


                return [
                    'product_code'=>$row->product_code,
                    'product_name'=>$row->product_name,
                    'gross_qty'=>$row->gross_qty,
                    'return_qty'=>$rtnQty,
                    'net_qty'=>$row->net_qty - $rtnQty,
                    'bdgt_value_new'=>round($netValue,2),
                    'bdgt_value'=>number_format($netValue,2)
                ];
            });

            $totGrossQty = $results->sum('gross_qty');
            $totReturnQty = $results->sum('return_qty');
            $totalQty = $results->sum('net_qty');
            $totalBudget = $results->sum('bdgt_value_new');

            $grand_total = [
                'product_code'=>'Grand Total',
                'product_name'=>'',
                'gross_qty'=>number_format($totGrossQty),
                'return_qty'=>number_format($totReturnQty),
                'net_qty'=>number_format($totalQty),
                'bdgt_value'=>number_format($totalBudget,2),
                'special'=>true
            ];

            $results->push($grand_total);

            return [
                'count'=>$count,
                'results'=>$results
            ];

    }

    public function setColumns($columnController, Request $request){
        $columnController->text('product_code')->setLabel("Product Code");
        $columnController->text('product_name')->setLabel("Product Name");
        $columnController->number('gross_qty')->setLabel("Gross Qty");
        $columnController->number('return_qty')->setLabel("Return Qty");
        $columnController->number('net_qty')->setLabel("Net Qty");
        $columnController->number('bdgt_value')->setLabel("Budget Value");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown('ar_id')->setLabel('Area')->setLink('area')->setValidations('');
        $inputController->ajax_dropdown('user')->setWhere(['u_tp_id'=>'2|3'.'|'.config('shl.product_specialist_type')])->setLabel('PS/MR & FM')->setLink('user')->setValidations('');
        $inputController->ajax_dropdown('sub_twn_id')->setLabel('Sub Town')->setLink('sub_town')->setValidations('');
        $inputController->ajax_dropdown('product_id')->setLabel('Product')->setLink('product')->setValidations('');
        $inputController->date("s_date")->setLabel('From');
        $inputController->date("e_date")->setLabel('To');

        $inputController->setStructure([
            ['ar_id','user'],
            ['sub_twn_id','product_id'],
            ['s_date','e_date']
        ]);
    }
}
