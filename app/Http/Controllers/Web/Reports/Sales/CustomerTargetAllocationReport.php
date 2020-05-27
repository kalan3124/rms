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
use App\Models\SalesmanValidPart;
use App\Models\User;

class CustomerTargetAllocationReport extends ReportController{

     protected $title = "Customer Target Allocation Report";

    public function search(Request $request){
        $values = $request->input('values');


        $query = SfaCustomerTarget::query();
        $query->with('chemist');

        if(isset($values['chemist'])){
            $query->where('sfa_cus_code',$values['chemist']['value']);
        }

        if(isset($values['user'])){
            $query->where('sfa_sr_code',$values['user']['value']);
        }

        if(isset($values['area'])){
            $area = Area::where('ar_code',$values['area']['value'])->first();
            if(isset($area->ar_code)){
                $users = User::where('u_code','LIKE','%'.$area->ar_code.'%')->get();
                $query->whereIn('sfa_sr_code',$users->pluck('id')->all());
            }
        }

        $query->where('sfa_year',date('Y',strtotime($values['month'])));
        $query->where('sfa_month',date('m',strtotime($values['month'])));
        $query->groupBy('sfa_cus_code');

        $count = $this->paginateAndCount($query,$request,'sfa_cus_code');
        $results = $query->get();

        // $query = SalesmanValidPart::query();
        // $query->select('product_id');
        // $query->whereIn('u_id',$results->pluck('sfa_sr_code')->all());
        // $query->where('from_date','<=',date('Y-m-01',strtotime($values['month'])));
        // $query->where('to_date','>=',date('Y-m-t',strtotime($values['month'])));
        // $products = $query->get();

        // $achivement = $this->makeQuery($products->pluck('product_id')->all(),$results->pluck('sfa_cus_code')->all(),date('Y-m-01',strtotime($values['month'])),date('Y-m-t',strtotime($values['month'])));
        // return $achivement->where('chemist_id');
        $results->transform(function($val){
            // $monthAchi = $achivement->where('chemist_id',$val->sfa_cus_code)->sum("amount");

            // if(isset($val->sfa_target) && $monthAchi > 0){
            //     $balance = $val->sfa_target - $monthAchi;
            // }

            // if($val->sfa_target > 0 && $monthAchi > 0){
            //     $achi_ = ($val->sfa_target / $monthAchi)*100;
            // }

            return[
                'chemist' => $val->chemist->chemist_code,
                'chemist_name' => $val->chemist->chemist_name.' - '.$val->sfa_sr_code,
                'target' => isset($val->sfa_target)?number_format($val->sfa_target,2):0,
                // 'achi' => isset($monthAchi)?round($monthAchi,2):0,
                // 'precentage' => isset($achi_)?round($achi_,2):0,
                // 'balance' => isset($balance)?round($balance,2):0
            ];
        });

        // $row = [
        //     'special' => true,
        //     'chemist' => NULL,
        //     'chemist_name' => NULL,
        //     'target' => number_format($results->sum('target'),2),
        //     'achi' => number_format($results->sum('achi'),2),
        //     'precentage' => number_format($results->sum('precentage'),2),
        //     'balance' => number_format($results->sum('balance'),2),
        // ];

        // $results->push($row);

        return[
            'results' => $results,
            'count' => $count
        ];
    }

    protected function makeQuery($products,$chemist,$fromDate,$toDate){

        $invoices = DB::table('invoice_line AS il')
          ->join('product AS p','il.product_id','=','p.product_id')
          ->join('chemist AS c','c.chemist_id','il.chemist_id')
          ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
          ->leftJoin('latest_price_informations AS pi',function($query){
              $query->on('pi.product_id','=','p.product_id');
              $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                        })
          ->select([
              'p.product_id',
              'il.chemist_id',
              'il.invoice_date',
              DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS gross_qty'),
              DB::raw('0 AS return_qty'),
              DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
              DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(il.invoiced_qty,0)) as bdgt_value'),
              DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
            ])
          ->whereIn('il.product_id',$products)
          ->whereIn('il.chemist_id',$chemist)
          ->whereDate('il.invoice_date','<=',$toDate)
          ->whereDate('il.invoice_date','>=',$fromDate)
          ->groupBy('il.chemist_id','il.product_id')
          ->get();


      $returns = DB::table('return_lines AS rl')
          ->join('product AS p','rl.product_id','=','p.product_id')
          ->join('chemist AS c','c.chemist_id','rl.chemist_id')
          ->join('sub_town AS st','st.sub_twn_id','c.sub_twn_id')
          ->leftJoin('latest_price_informations AS pi',function($query){
              $query->on('pi.product_id','=','p.product_id');
              $query->on('pi.year','=',DB::raw('IF(MONTH(rl.last_updated_on)<4,YEAR(rl.last_updated_on)-1,YEAR(rl.last_updated_on))'));
                        })
          ->select([
            'p.product_id',
              'rl.chemist_id',
              'rl.invoice_date',
              DB::raw('0 AS gross_qty'),
              DB::raw('IFNULL(SUM(rl.invoiced_qty),0) AS return_qty'),
              DB::raw('0 AS net_qty'),
              DB::raw('0 AS bdgt_value'),
              DB::raw('sum(ifnull(if(pi.lpi_bdgt_sales = "0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * ifnull(rl.invoiced_qty,0)) as rt_bdgt_value'),
              DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
              ])
            ->whereIn('rl.product_id',$products)
          ->whereIn('rl.chemist_id',$chemist)
          ->whereDate('rl.invoice_date','<=',$toDate)
          ->whereDate('rl.invoice_date','>=',$fromDate)
          ->groupBy('rl.chemist_id','rl.product_id')
          ->get();

          $allProducts = $invoices->merge($returns);
          $allProducts->all();

          $allProducts = $allProducts->unique(function ($item) {
              return $item->chemist_id.$item->product_id;
          });
          $allProducts->values()->all();

          $results = $allProducts->values();
          $results->all();

          $results->transform(function($row)use($results,$returns){
                $netValue = 0;
                foreach ($results AS $inv){
                    if($row->product_id == $inv->product_id && $row->chemist_id == $inv->chemist_id){
                        $netValue += $inv->bdgt_value;
                    }
                }
                foreach ($returns AS $rtn){
                    if($row->product_id == $rtn->product_id && $row->chemist_id == $rtn->chemist_id){
                        $netValue -= $rtn->rt_bdgt_value;
                    }
                }


                return [
                    'amount'=>round($netValue,2),
                    'chemist_id' => $row->chemist_id,
                ];
          });

      return $results;
   }

    public function setColumns(ColumnController $columnController, Request $request){
        $columnController->text("chemist")->setLabel("Customer");
        $columnController->text("chemist_name")->setLabel("Customer Name");
        $columnController->number("target")->setLabel("Target");
        // $columnController->number("achi")->setLabel("Achivement");
        // $columnController->number("precentage")->setLabel("%");
        // $columnController->number("balance")->setLabel("Balance");
    }

    public function setInputs($inputController){
        $inputController->ajax_dropdown("area")->setLabel("Area")->setLink("area")->setValidations('');
        $inputController->ajax_dropdown("user")->setLabel("SR")->setValidations('');
        $inputController->ajax_dropdown("chemist")->setLabel("Customer")->setLink("chemist")->setValidations('');
        $inputController->date("month")->setLabel("Month");

        $inputController->setStructure([["area","user","chemist","month"]]);
    }
}
?>
