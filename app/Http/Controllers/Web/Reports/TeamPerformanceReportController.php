<?php
namespace App\Http\Controllers\Web\Reports;

use App\Http\Controllers\Controller;
use App\Models\InvoiceLine;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Traits\Territory;
use App\Models\TeamUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeamPerformanceReportController extends ReportController{

    use Territory;

    public function search(Request $request){
        $values = $request->input('values',[]);

        $query =  DB::table('teams as t')
                ->select('t.tm_name','t.tm_id','p.product_id','p.product_name','p.product_code','p.principal_id','t.hod_id')
                ->join('team_products AS tp','tp.tm_id','t.tm_id')
                ->join('product AS p','p.product_id','tp.product_id')
                ->whereNull('t.deleted_at')
                ->whereNull('p.deleted_at')
                ->whereNull('tp.deleted_at');

                if(isset($values['team'])){
                    $query->where('t.tm_id',$values['team']['value']);
                }

                if(isset($values['principal'])){
                    $query->where('p.principal_id',$values['principal']['value']);
                }

        $count = $this->paginateAndCount($query,$request,'tm_name');
        $results = $query->get();

        $data =  $this->getHodDetails();

        $formattedResults = [];
        foreach ($results as $key => $row) {

            $productDecQtyTot = 0;
            $productDecSalesTot = 0;
            $productJanQtyTot = 0;
            $productJanSalesTot = 0;
            $productLastJanQtyTot = 0;
            $productLastJanSalesTot = 0;

            $team_users = TeamUser::where('tm_id',$row->tm_id)->get();
            foreach ($team_users as $key => $team_user) {
                $user = User::find($team_user->u_id);
                $towns = $this->getAllocatedTerritories($user);

                $productDecSales = $this->makeQuery($towns,date('Y-12-01',strtotime('-1 Year')),date('Y-12-t',strtotime('-1 Year')),$team_user->tm_id,$team_user->u_id);
                $productDecsalesAchi =  $productDecSales->where('product_id',$row->product_id)->first();

                $productDecQtyTot += $productDecsalesAchi?$productDecsalesAchi['qty']:0;
                $productDecSalesTot += $productDecsalesAchi?$productDecsalesAchi['amount']:0;

                $productJanSales = $this->makeQuery($towns,date('Y-01-01'),date('Y-01-t'),$team_user->tm_id,$team_user->u_id);
                $productJansalesAchi =  $productJanSales->where('product_id',$row->product_id)->first();

                $productJanQtyTot += $productJansalesAchi?$productJansalesAchi['qty']:0;
                $productJanSalesTot += $productJansalesAchi?$productJansalesAchi['amount']:0;

                $productLastJanSales = $this->makeQuery($towns,date('Y-01-01',strtotime('-1 Year')),date('Y-01-t',strtotime('-1 Year')),$team_user->tm_id,$team_user->u_id);
                $productLastJansalesAchi =  $productLastJanSales->where('product_id',$row->product_id)->first();

                $productLastJanQtyTot += $productLastJansalesAchi?$productLastJansalesAchi['qty']:0;
                $productLastJanSalesTot += $productLastJansalesAchi?$productLastJansalesAchi['amount']:0;
            }
            $formattedResults [] = [
                'tm_id' => $row->tm_id,
                'tm_name'=> $row->tm_name,
                'pro_code'=> $row->product_code,
                'pro_name'=> $row->product_name,
                'dec_net_qty'=> $productDecQtyTot,
                'dec_net_sales'=> $productDecSalesTot,
                'jan_net_qty'=> $productJanQtyTot,
                'jan_net_sales'=> number_format($productJanSalesTot,2),
                'qty'=> $productDecQtyTot > 0 && $productJanQtyTot > 0?$productDecQtyTot - $productJanQtyTot:0,
                'value'=> $productDecSalesTot > 0 && $productJanSalesTot > 0?$productDecSalesTot - $productJanSalesTot:0,

                'null_colum'=>'',

                'last_jan_sale_qty' => $productLastJanQtyTot,
                'last_jan_sale_value' => number_format($productLastJanSalesTot,2),
                'last_jan_sale_qty_growth' => 0,
                'last_jan_sale_value_growth' => 0,
                'hod_id' => $row->hod_id
            ];
        }

        return[
            'results' => $formattedResults,
            'hod' => $data,
            'count' => $count
        ];
    }

    protected function getHodDetails(){
        $data = [];
        $hods = User::where('u_tp_id',4)->get();

        foreach ($hods as $key => $row) {
            // $team = Team::where('hod_id',$row->id)->get();

            // foreach ($team as $key => $val) {
                $data[] = [
                    "hod_id" =>  $row->id,
                    "hod_name" =>  $row->name,
                    // "team_id" => isset($val->tm_id)?$val->tm_id:null,
                    // "team_name" => isset($val->tm_name)?$val->tm_name:null
                ];
            // }
        }
        return $data;
    }
    protected function makeQuery($towns,$fromDate,$toDate,$teamId,$userId){

        $invoices = InvoiceLine::bindSalesAllocation(DB::table('invoice_line AS il'),$userId)
            ->join('product AS p','il.product_id','=','p.product_id')
            // ->join('sub_town AS st','st.sub_twn_code','il.city')
            // ->join('sub_town AS st','st.sub_twn_id','il.sub_twn_id')
            ->join('chemist AS c','c.chemist_id','il.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
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
            // ->whereIn('il.city',$towns->pluck('sub_twn_code')->all())
            ->whereIn('c.sub_twn_id',$towns->pluck('sub_twn_id')->all())
            ->whereDate('il.invoice_date','<=',$toDate)
            ->whereDate('il.invoice_date','>=',$fromDate)
            ->groupBy('il.product_id')
            ->get();


        $returns = InvoiceLine::whereWithSalesAllocation(InvoiceLine::bindSalesAllocation(DB::table('return_lines AS rl'),$userId,true)
            ->join('product AS p','rl.product_id','=','p.product_id')
            // ->join('sub_town AS st','st.sub_twn_code','rl.city')
            // ->join('sub_town AS st','st.sub_twn_id','rl.sub_twn_id')
            ->join('chemist AS c','c.chemist_id','rl.chemist_id')
            ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
            ->leftJoin('latest_price_informations AS pi',function($query){
                $query->on('pi.product_id','=','p.product_id');
                $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                            })
            ->select([
                'rl.identity',
                'rl.product_id',
                'p.product_code',
                'p.product_name',
                InvoiceLine::salesAmountColumn('rt_bdgt_value',true),
                InvoiceLine::salesQtyColumn('return_qty',true),
                DB::raw('0 AS gross_qty'),
                DB::raw('IFNULL(SUM(rl.invoiced_qty),0) AS return_qty'),
                DB::raw('0 AS net_qty'),
                DB::raw('0 AS bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ]),'c.sub_twn_id',$towns->pluck('sub_twn_id')->all(),true)
            ->whereDate('rl.invoice_date','<=',$toDate)
            ->whereDate('rl.invoice_date','>=',$fromDate)
            ->groupBy('rl.product_id')
            ->get();

            $allProducts = $invoices->merge($returns);
            $allProducts->all();

            $allProducts = $allProducts->unique(function ($item) {
                return $item->product_code;
            });
            $allProducts->values()->all();

            $results = $allProducts->values();
            $results->all();

            $results->transform(function($row)use($results,$returns){
                $grossQty = 0;
                $netQty = 0;
                $rtnQty = 0;
                $netValue = 0;
                foreach ($results AS $inv){
                    if($row->product_id == $inv->product_id){
                        $netValue += $inv->bdgt_value;
                        $netQty += $inv->net_qty;
                    }
                }
                foreach ($returns AS $rtn){
                    if($row->product_id == $rtn->product_id){
                        $netValue -= $rtn->rt_bdgt_value;
                        $netQty -= $rtn->return_qty;
                    }
                }


                return [
                    'product_id'=>$row->product_id,
                    'qty'=>$netQty,
                    'amount'=>round($netValue,2)
                ];
            });

        return $results;
    }
}
?>
