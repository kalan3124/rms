<?php 
namespace App\Http\Controllers\API\Distributor\V1;

use App\Http\Controllers\Controller;
use App\Models\DistributorReturn;
use App\Models\DistributorReturnItem;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DistributorReturnController extends Controller{
     
     public function saveReturn(Request $request){
          $user = Auth::user();

          $return = json_decode($request['jsonString'],true);
          
          // Java Timestamp = PHP Unix Timestamp * 1000
        $order_timestamp = $return['return']['timestamp'] / 1000;

        // Formating the unix timestamp to a string
        $order_time = date("Y-m-d H:i:s", $order_timestamp);

          $return_id = DistributorReturn::create([
               'invoice_type' => $return['return']['invoice_type'],
               'invoice_mode' => $return['return']['invoice_mode'],
               'discount' => $return['return']['discount'],
               'latitude' => $return['return']['longitude'],
               'longitude' => $return['return']['latitude'],
               'return_date' => $order_time,
               'dis_id' => $return['return']['distributorId'],
               'dc_id' => $return['return']['outletId'],
               'batteryLevel' => $return['return']['batteryLevel'],
               'dist_return_number' => $return['return']['orderId'],
          ]);

          foreach ($return['return']['returnItems'] as $key => $value) {
               DistributorReturnItem::create([
                    'rsn_id' => $value['reasonId'],
                    'dis_return_id' => $return_id->getKey(),
                    'product_id' => $value['itemId'],
                    'dri_qty' => $value['qty'],
               ]);
          }

          return response()->json([
               'result'=>true,
               'message'=>'Return successfully added.'
          ]);
     }
}