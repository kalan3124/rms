<?php
namespace App\Http\Controllers\API\Sales\V1;

use App\Http\Controllers\Controller;
use App\Models\Chemist;
use App\Models\SalesmanValidCustomer;
use App\Models\SfaSalesOrder;
use App\Models\SfaSalesOrderProduct;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\SalesTerritory;

class SuggestiveOrderController extends Controller{

     use SalesTerritory;

     public function createSuggestOrder(){

          $user = Auth::user();
          $getAllocatedTerritories = $this->getRoutesByItinerary($user);

          $sale_chemist = Chemist::whereIn('route_id',$getAllocatedTerritories->pluck('route_id')->all())->get();

          // $sale_chemist = SalesmanValidCustomer::where('u_id',$user->getKey())->get();

          $query = DB::table('sfa_sales_order as so');
          $query->select([
               'sop.product_id',
               'so.chemist_id',
               DB::raw('SUM(sop.sales_qty) AS sum')
          ]);
          $query->join('sfa_sales_order_product as sop','sop.order_id','so.order_id');
          $query->whereIn('so.chemist_id',$sale_chemist->pluck('chemist_id')->all());
          $query->whereDate('so.order_date','>=',date('Y-m-01',strtotime((new \Carbon\Carbon)->submonths(3))));
          $query->whereDate('so.order_date','<=',date('Y-m-t',strtotime((new \Carbon\Carbon)->submonths(1))));
          $query->whereNotNull('sales_order_amt');
          $query->groupBy('sop.product_id','so.chemist_id');
          $query->orderBy('so.chemist_id');
          $results = $query->get();

          $formatedResults = [];
          $lastChemistId = 0;
          $lastProducts =[];

          $results->push((object)['product_id'=>0,'chemist_id'=>0,'sum'=>0]);

          foreach ($results as $key => $result) {
               if($lastChemistId!=$result->chemist_id){
                    $formatedResults[] = [
                         'chemist_id'=>$result->chemist_id,
                         'products'=>$lastProducts
                    ];

                    $lastProducts = [];
               }

               $lastProducts[] = [
                    'product_id'=>$result->product_id,
                    'sales_qty'=>$result->sum
               ];

               $lastChemistId = $result->chemist_id;
          }

          return [
               'result' => true,
               'data' => $formatedResults
          ];
     }
}
?>