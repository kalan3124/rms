<?php
namespace App\Http\Controllers\Web\Reports;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\ProductiveSampleDetails;
use \DateTime;
use DateInterval;
use DatePeriod;
use Illuminate\Database\Query\JoinClause;


class YtdProductReportController extends ReportController
{
     protected $title = "YTD Product Report";

     public function search(Request $request){

          $products_arr = [];
          $products_qty = [];

          $values = $request->input('values');

          $qty_val =  $values['qty_value'];
          $team_id =  $values['team_id'];
          // return $team_id['value'];

          $year =  date("Y",strtotime($values['s_date']));

          $team_product = DB::table('team_products AS tp')
          ->join('product AS p','tp.product_id','p.product_id')
          ->select('*');
          // ->where('tp.tm_id',$team_id)


          if($team_id['value']){
               $team_product->where('tp.tm_id',$team_id);
          }
          $team_product->get();

          for ($m=1; $m<=12; $m++) {
               $month[] = date('F', mktime(0,0,0,$m, 1, date('Y')));
          }
          for($i=0; $i<= date('m')-2; $i++){

               $productWiseSalesForMonth = [];

               $month_value =  DB::table('invoice_line AS il')
               ->join('invoice AS i',function(JoinClause $join){
               $join->on('il.inv_head_id','i.inv_head_id');
               })
               ->join('product AS p','il.product_id','=','p.product_id')
               ->leftJoin('latest_price_informations AS pi',function($query){
                   $query->on('pi.product_id','=','p.product_id');
                   $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                  })
               ->leftJoin('return_lines AS rl',function(JoinClause $join){
               $join->on('i.inv_head_id','=','rl.inv_head_id');
               $join->on('p.product_id','=','rl.product_id');
               })
               ->select([
               'p.product_id',
               'p.product_name',
                    DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS gross_qty'),
                    DB::raw('IFNULL(SUM(il.invoiced_qty),0) -  IFNULL(SUM(rl.invoiced_qty),0) AS net_qty'),
                    DB::raw('ROUND(IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * Ifnull(Sum(il.invoiced_qty), 0) - Ifnull(Sum(rl.invoiced_qty), 0),2) AS bdgt_value')
               ])
               ->whereYear('il.last_updated_on',date('Y'))
               ->whereMonth('il.last_updated_on',$i)
               ->whereIn('pi.product_id',$team_product->pluck('product_id')->all())
               ->groupBy('p.product_id')
               ->get();

               // print_r($month_value);

               // $productWiseSalesForMonth = $month_value;

               foreach($month_value as $product_data){
                     $products_arr[$product_data->product_id][$month[$i].'-'.$year] = $product_data->bdgt_value;
               }
               // print_r($products_qty);
          }

          $current_month_value =  DB::table('invoice_line AS il')
               ->join('invoice AS i',function(JoinClause $join){
               $join->on('il.inv_head_id','i.inv_head_id');
               })
               ->join('product AS p','il.product_id','=','p.product_id')
               ->leftJoin('latest_price_informations AS pi',function($query){
                   $query->on('pi.product_id','=','p.product_id');
                   $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                  })
               ->leftJoin('return_lines AS rl',function(JoinClause $join){
               $join->on('i.inv_head_id','=','rl.inv_head_id');
               $join->on('p.product_id','=','rl.product_id');
               })
               ->select([
               'p.product_id',
               'p.product_name',
                    DB::raw('IFNULL(SUM(il.invoiced_qty),0) AS gross_qty'),
                    DB::raw('IFNULL(SUM(il.invoiced_qty),0) -  IFNULL(SUM(rl.invoiced_qty),0) AS net_qty'),
                    DB::raw('ROUND(IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * Ifnull(Sum(il.invoiced_qty), 0) - Ifnull(Sum(rl.invoiced_qty), 0),2) AS bdgt_value')
               ])
               ->whereYear('il.last_updated_on',date('Y'))
               ->whereMonth('il.last_updated_on',date('m'))
               ->whereIn('pi.product_id',$team_product->pluck('product_id')->all())
               ->groupBy('p.product_id')
               ->get();

               // print_r($current_month_value);

          $results = [];

          $products_new = Product::whereIn('product_id',array_keys($products_arr))->get();

          foreach($products_arr as $product_id=>$monthWiseSale){
               $count = 0;
               $sumOfQtyOrValues = 0;
               $product = $products_new->where('product_id',$product_id)->first();

               $productWiseRow = [];

               $productWiseRow['product_name'] = $product->product_name;

               $data = $current_month_value->where('product_id',$product_id)->first();
               $data_net = $month_value->where('product_id',$product_id)->first();

               $productWiseRow['cur_month_sales'] = $data?$data->bdgt_value:0;



               if($qty_val['value'] == 0){
                    for($i=0; $i<= date('m')-2; $i++){
                         $productWiseRow[$month[$i]] = isset($monthWiseSale[$month[$i].'-'.$year])?$data_net->net_qty:0;

                         if($productWiseRow[$month[$i]] != 0){
                              $sumOfQtyOrValues += $data_net->net_qty;
                              $count++;
                         }

                    }
               }
               if($qty_val['value'] == 1){

                    for($i=0; $i<= date('m')-2; $i++){
                         $productWiseRow[$month[$i]] = isset($monthWiseSale[$month[$i].'-'.$year])?$monthWiseSale[$month[$i].'-'.$year]:0;

                         if($productWiseRow[$month[$i]] != 0){
                              $sumOfQtyOrValues += $productWiseRow[$month[$i]];
                              $count++;
                         }

                    }
                    // print_r($count);
               }



               // $productWiseRow['qty_or_value'] = $data_net->net_qty;
               $productWiseRow['qty_or_value'] = $sumOfQtyOrValues;
               $productWiseRow['avg'] = $sumOfQtyOrValues/$count;


               $results[] = $productWiseRow;
          }

          return [
               'count'=>0,
               'results'=> $results
           ];
     }

     protected function setColumns($columnController, Request $request){
          $columnController->text('product_name')->setLabel("Product");

          for ($m=1; $m<=12; $m++) {
               $month[] = date('F', mktime(0,0,0,$m, 1, date('Y')));
          }
          for($i=0; $i<= date('m')-2; $i++){
               $columnController->text($month[$i])->setLabel($month[$i]);
          }

          $columnController->text('qty_or_value')->setLabel("QTY/Value");
          $columnController->text('avg')->setLabel("AVG");
          $columnController->text('cur_month_sales')->setLabel("Current month sales");
          $columnController->text('com_to_month')->setLabel("Growth % Compare to month");
     }

     protected function setInputs($inputController){

          $inputController->ajax_dropdown("team_id")->setLabel("Team")->setLink("team");
          $inputController->select("qty_value")->setLabel("Qty or Value")->setOptions([
               0 => "Qty",
               1 => "Value"
          ]);
          $inputController->date("s_date")->setLabel("Last Month");
          $inputController->date("e_date")->setLabel("To");
          $inputController->setStructure([
               ["team_id","qty_value","s_date"]
               ]);

     }

}

?>
