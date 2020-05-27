<?php
namespace App\CSV;

use App\Exceptions\WebAPIException;
use App\Models\Area;
use App\Models\Product;
use App\Models\ProductLatestPriceInformation;
use App\Models\PurchaseOrder as ModelsPurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Base{
    protected $title = "Purchase Order";

    protected $lastUser = 0;

     protected $columns = [
          'purchase_no'=>'Purchase NO',
          'dsr_code'=>"Dsr Code",
          'dis_code'=>"Distributor Code",
          'site_code'=>"Site Code",
          'pro_code'=>'Poduct Code',
          'qty'=>'Qty'
     ];

     protected function formatValue($columnName, $value){
          switch ($columnName) {
               case 'dsr_code':
                   if(!$value)
                       throw new WebAPIException("Please provide a Dsr code!");
                   $user = User::where((new User)->getCodeName(),"LIKE",$value)->first();
                   if(!$user)
                       throw new WebAPIException("User not found! Given dsr code is '$value'");
                   return $user->getKey();
               case 'dis_code':
                    if(!$value)
                        throw new WebAPIException("Please provide a Distributor code!");
                    $user = User::where((new User)->getCodeName(),"LIKE",$value)->first();
                    if(!$user)
                        throw new WebAPIException("User not found! Given distributor code is '$value'");
                    return $user->getKey();
               case 'site_code': 
                   if(!$value)
                       throw new WebAPIException("Please provide a Site code!");
                   $site = Site::where((new Site)->getCodeName(),"LIKE",$value)->first();
                   if(!$site)
                       throw new WebAPIException("Site not found! Given site code is '$value'");
                   return $site->getKey();
               case 'pro_code': 
                    if(!$value)
                        throw new WebAPIException("Please Product a Site code!");
                    $product = Product::where((new Product)->getCodeName(),"LIKE",$value)->first();
                    if(!$product)
                        throw new WebAPIException("Site not found! Given product code is '$value'");
                    return $product->getKey();
               default:
                   return ($value<=0||!$value)?null:$value;
          }
     }

     protected function insertRow($row){
          $user = Auth::user();
          
          $purchaseOrder = ModelsPurchaseOrder::where('dis_id',$row['dis_code'])->latest()->first();

          if($purchaseOrder->po_number == $row['purchase_no']){
               throw new WebAPIException("Purchase order number same as with last Purchase order number");
          }

          if($this->lastUser==$row['dis_code']){

               $purchaseOrder = ModelsPurchaseOrder::where('dis_id',$row['dis_code'])->latest()->first();

               $latestPrice = ProductLatestPriceInformation::where('product_id',$row['pro_code'])->latest()->first();

               PurchaseOrderLine::create([
                    'po_id'=>$purchaseOrder->getKey(),
                    'product_id'=>$row['pro_code'],
                    'pol_qty'=>$row['qty'],
                    'pol_price'=>$latestPrice?($latestPrice->lpi_bdgt_sales>0?$latestPrice->lpi_bdgt_sales:$latestPrice->lpi_pg01_sales):0.00,
                    'pol_amount'=>$latestPrice?($latestPrice->lpi_bdgt_sales>0?($latestPrice->lpi_bdgt_sales*$row['qty']):($latestPrice->lpi_pg01_sales*$row['qty'])):0.00
               ]);

          } else {
                    
               try{
                    DB::beginTransaction();

                    $purchaseOrder = ModelsPurchaseOrder::create([
                         'dis_id'=>$row['dis_code'],
                         'created_u_id'=>$user->getKey(),
                         'po_number'=>$row['purchase_no'],
                         'po_amount'=>0,
                         'sr_id'=>$row['dsr_code'],
                         'site_id'=>$row['site_code']
                    ]);
     
                    $latestPrice = ProductLatestPriceInformation::where('product_id',$row['pro_code'])->latest()->first();
     
                    PurchaseOrderLine::create([
                         'po_id'=>$purchaseOrder->getKey(),
                         'product_id'=>$row['pro_code'],
                         'pol_qty'=>$row['qty'],
                         'pol_price'=>$latestPrice?($latestPrice->lpi_bdgt_sales>0?$latestPrice->lpi_bdgt_sales:$latestPrice->lpi_pg01_sales):0.00,
                         'pol_amount'=>$latestPrice?($latestPrice->lpi_bdgt_sales>0?($latestPrice->lpi_bdgt_sales*$row['qty']):($latestPrice->lpi_pg01_sales*$row['qty'])):0.00
                    ]);

                    $purchaseOrder->sendToIFS();

                    DB::commit();
               } catch (\Exception $e){
                    DB::rollBack();
                    throw $e;
               }
          }

          $this->lastUser = $row['dis_id'];
     }
}
?>