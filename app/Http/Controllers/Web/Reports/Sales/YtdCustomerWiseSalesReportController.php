<?php
namespace App\Http\Controllers\Web\Reports\Sales;

use App\Form\Columns\ColumnController;
use App\Http\Controllers\Web\Reports\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Chemist;
use App\Models\ChemistClass;

class YtdCustomerWiseSalesReportController extends ReportController{
     protected $title = "YTD Customer wise Sales Report";

     protected $updateColumnsOnSearch = true;

     public function search(Request $request){
          $values = $request->input('values',[]);

          $chemists = Chemist::query();
          $chemists->with('sub_town');

          if(isset($values['chemist_id'])){
               $chemists->where('chemist_id',$values['chemist_id']);
          }

          if(isset($values['sub_twn_id'])){
               $chemists->where('sub_twn_id',$values['sub_twn_id']);
          }

          $count = $this->paginateAndCount($chemists,$request,'chemist_id');
          $results = $chemists->get();

          $achivement = $this->makeQuery($results->pluck('chemist_id')->all(),date('Y-m-d',strtotime('2019-01-01')),date('Y-m-d',strtotime('2019-12-31')));
          // return $achivement;
          $begin = new \DateTime(date('Y-m-d',strtotime($values['s_date'])));
          $end = new \DateTime(date('Y-12-t',strtotime($values['s_date'])));

          $interval = \DateInterval::createFromDateString('1 month');
          $period = new \DatePeriod($begin, $interval, $end);

          $achiAmount = 0;
          $results->transform(function($row,$key) use($achivement,$period){
               if(isset($achivement)){

                    foreach ($period as $key => $month) {
                         $monthAchi = $achivement->where('chemist_id',$row->chemist_id)->where('invoice_date',$month->format('m'))->sum("amount");
                         $achiAmount = isset($monthAchi)?number_format($monthAchi,2):0;
                         $return['month_'.$month->format('m')] = $achiAmount;
                    }
               }

               $class = ChemistClass::where('chemist_class_id',$row->chemist_class_id)->first();

               $return['ifs_code'] = $row->chemist_code;
               $return['cus_name'] = $row->chemist_name;
               $return['town'] = isset($row->sub_town->sub_twn_name)?$row->sub_town->sub_twn_name:"-";
               $return['class'] = isset($class->chemist_class_name)?$class->chemist_class_name:"-";
               return $return;
          });

          return[
               'results' => $results,
               'count' => $count
          ];
     }

     protected function makeQuery($towns,$fromDate,$toDate){

          $invoices = DB::table('invoice_line AS il')
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
                'p.principal_id',
                'c.sub_twn_id',
                'c.chemist_id',
                'il.invoice_date',
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS gross_qty'),
                DB::raw('0 AS return_qty'),
                DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS net_qty'),
                DB::raw('ROUND(IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * Ifnull(Sum(il.invoiced_qty), 0),2) AS bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ])
          //   ->whereIn('il.city',$towns->pluck('sub_twn_code')->all())
            ->whereIn('c.chemist_id',$towns)
            ->whereDate('il.invoice_date','<=',$toDate)
            ->whereDate('il.invoice_date','>=',$fromDate)
            ->groupBy('il.product_id')
            ->get();


        $returns = DB::table('return_lines AS rl')
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
                'p.principal_id',
                'c.sub_twn_id',
                'c.chemist_id',
                'rl.invoice_date',
                DB::raw('0 AS gross_qty'),
                DB::raw('IFNULL(SUM(rl.invoiced_qty),0) AS return_qty'),
                DB::raw('0 AS net_qty'),
                DB::raw('0 AS bdgt_value'),
                DB::raw('IFNULL(Sum(rl.invoiced_qty * IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0)), 0) AS rt_bdgt_value'),
                DB::raw('IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) AS budget_price')
                ])
          //   ->whereIn('rl.city',$towns->pluck('sub_twn_code')->all())
            ->whereIn('c.chemist_id',$towns)
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
                    'amount'=>round($netValue,2),
                    'principal_id'=>$row->principal_id,
                    'sub_twn_id' => $row->sub_twn_id,
                    'chemist_id' => $row->chemist_id,
                    'invoice_date' => date('m',strtotime($row->invoice_date))
                ];
            });

        return $results;
     }

     public function setColumns(ColumnController $columnController, Request $request){
          $values = $request->input('values',[]);

          $columnController->text('ifs_code')->setLabel('IFS Code');
          $columnController->text('cus_name')->setLabel('Customer Name');
          $columnController->text('town')->setLabel('Town');
          $columnController->text('class')->setLabel('New Customer Class');

          if(isset($values['s_date'])){
               $begin = new \DateTime(date('Y-m-d',strtotime($values['s_date'])));
               $end = new \DateTime(date('Y-12-t',strtotime($values['s_date'])));

               $interval = \DateInterval::createFromDateString('1 month');
               $period = new \DatePeriod($begin, $interval, $end);

               foreach ($period as $key => $month) {
                    $columnController->text('month_'.$month->format('m'))->setLabel($month->format('M'));
               }
          }
     }

     public function setInputs($inputController){
          $inputController->ajax_dropdown('chemist_id')->setLabel('Chemist')->setLink('chemist')->setValidations('');
          $inputController->ajax_dropdown('sub_twn_id')->setLabel('Sub Town')->setLink('subTown')->setValidations('');
          $inputController->date('s_date')->setLabel('Month')->setLink('s_date');
          $inputController->date('e_date')->setLabel('To')->setLink('e_date');

          $inputController->setStructure([
               ['chemist_id','sub_twn_id','s_date']
          ]);
     }
}
?>
