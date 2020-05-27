<?php 
namespace App\Http\Controllers\API\Distributor\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \Illuminate\Support\Facades\Auth;
use App\Models\DistributorCustomer;
use App\Models\DistributorSalesOrder;
use App\Models\DistributorSalesOrderBonusProduct;
use App\Models\DistributorSalesOrderProduct;
use App\Models\UserAttendance;

class SalesOrderController extends  Controller{

    public function save(Request $request){

        // Decoding the json
        $json_decode = json_decode($request['jsonString'], true);
        
        // Getting the logged user
        $user = Auth::user();

        $json_decode = $json_decode['Invoice'];

        $appVersion = null;
        if(isset($request['appVersion'])){
            $appVersion = $request['appVersion'];
        }

        // Java Timestamp = PHP Unix Timestamp * 1000
        $order_timestamp = $json_decode['timestamp'] / 1000;

        // Formating the unix timestamp to a string
        $order_time = date("Y-m-d H:i:s", $order_timestamp);

        // Getting the last attendance record related to user
        $data = [
            'u_id'=> $user->getKey(),
            'dc_id'=> $json_decode['outletId'],
            'order_date'=> $order_time,
            'order_no'=>$json_decode['orderId']
        ];
        $ckSalesOrder = DistributorSalesOrder::where($data)
                    ->latest()
                    ->first();

        if($ckSalesOrder){
            return [
                'result'=>true,
                "message" => "Sales order has been already added"
            ];
            // throw new MediAPIException('Sales order has been already added', 15);
        }else{ 
            
            $chemist = DistributorCustomer::with(['sub_town','sub_town.town','sub_town.town.area','sub_town.town.area.region'])->where('dc_id',$json_decode['outletId'])->first();
                
            $salesOrder = DistributorSalesOrder::create([
                'order_no'=>$json_decode['orderId'],
                'u_id'=>$user->getKey(),
                'order_date'=>$order_time,
                'order_mode'=>$json_decode['invoice_mode'],
                'order_type'=>$json_decode['invoice_type'],
                'dc_id'=>$json_decode['outletId'],
                'latitude'=>$json_decode['latitude'],
                'longitude'=>$json_decode['longitude'],
                'battery_lvl'=>$json_decode['batteryLevel'],
                'app_version'=>$appVersion,
                'contract'=>'',
                'dis_id' => $json_decode['distributorId'],
                'discount' => $json_decode['discount'],
                'remark'=> isset($json_decode['remark'])?$json_decode['remark']:null
            ]);

            $total_discount = 0;

            $order_id = $salesOrder->getKey();
            $order_amount = 0;
            foreach($json_decode['invoiceItems'] AS $pro){

                $salesProduct = DistributorSalesOrderProduct::create([
                    'dist_order_id'=>$order_id,
                    'product_id'=>$pro['itemId'],
                    'sales_qty'=>$pro['qty'],
                    'price'=>$pro['price'],
                    'di_discount' => isset($pro['discountPercentage'])?$pro['discountPercentage']:0
                ]);

                $order_amount += $pro['qty'] * $pro['price'];
                $total_discount += $pro['qty']* $pro['price'] *$pro['discountPercentage'] / 100;
            }

            if(isset($json_decode['freeItems'])){
                foreach($json_decode['freeItems'] AS $bns){
                    DistributorSalesOrderBonusProduct::create([
                        'dist_order_id'=>$order_id,
                        'product_id'=>$bns['itemId'],
                        'dsobp_qty'=>$bns['qty'],
                        'bns_id'=>$bns['freeId'],
                    ]);
                }
            }

            $salesOrder->sales_order_amt = $order_amount;
            $salesOrder->sub_twn_id = $chemist->sub_twn_id;
            $salesOrder->ar_id = $chemist->sub_town->town->area->ar_id;
            $salesOrder->discount = $total_discount;
            $salesOrder->save();
        }

        return response()->json([
            'result'=>true,
            'message'=>'Sales order has successfully added.'
        ]);
    }

    public function dailySaleOrders(Request $request){

        $user = Auth::user();

        $attendance = UserAttendance::where('u_id', $user->getKey())
                    ->latest()
                    ->first();
                    
        if(!$attendance->checkout_status){
            $orders = DistributorSalesOrder::with(['salesOrderProducts','distributorCustomer','distributorCustomer.sub_town'])
                ->where('u_id',$user->getKey())
                ->whereDate('order_date','>=',date('Y-m-d',strtotime($attendance->check_in_time)))
                ->whereDate('order_date','<=',date('Y-m-d',strtotime($attendance->check_in_time)))
                ->get();
        }

        $orders->transform(function($order){

            $items = $order->salesOrderProducts->transform(function($val){
                return[
                    'item_id' => $val->product_id,
                    'qty' => $val->sales_qty,
                    'price' => $val->price,
                    'discountPercentage' => isset($val->di_discount)?$val->di_discount:0
                ];
            });

            return[
                'order_no' => $order->order_no,
                'u_id' => $order->u_id,
                'order_date' => $order->order_date,
                'order_no' => $order->order_no,
                'order_mode' => $order->order_mode,
                'order_type' => $order->order_type,
                'chemist_id' => $order->dc_id,
                'latitude' => $order->latitude,
                'longitude' => $order->longitude,
                'battery_lvl' => $order->battery_lvl,
                'app_version' => $order->app_version,
                'contract' => $order->contract,
                'distributorId' => $order->dis_id,
                'items' => $items,
                'chemist' => [
                    "chem_id"=> $order->distributorCustomer->dc_id,
                    "chem_name"=> $order->distributorCustomer->dc_name,
                    "chem_code"=> $order->distributorCustomer->dc_code,
                    "chem_address"=> $order->distributorCustomer->dc_address,
                    'chem_price_group'=>"",
                    'chem_price_list'=>"",
                    "chem_class_id"=> 0,
                    "chem_type_id"=> 0,
                    "chem_type"=> "ANY",
                    "chem_market_description_id"=> 0,
                    "chem_market_description" => "ANY",
                    "town_id"=> $order->distributorCustomer->sub_town? $order->distributorCustomer->sub_town->sub_twn_id:0,
                    "town_name"=> $order->distributorCustomer->sub_town? $order->distributorCustomer->sub_town->sub_twn_name:"",
                    "route_id"=> 0,
                    "mobile_number"=> 0,
                    "phone_no"=> 0,
                    "chemist_owner"=> "",
                    "credit_limit"=> 0.00,
                    "email"=> "",
                    "lat"=> $order->distributorCustomer->lat,
                    "lon"=> $order->distributorCustomer->lon,
                    "updated_u_id"=> 0,
                    "image_url"=> $order->distributorCustomer->dc_image_url?url($order->distributorCustomer->dc_image_url):""
                ]
            ];
        });

        return [
            'result'=>true,
            "orders" => $orders
        ];
    }
}