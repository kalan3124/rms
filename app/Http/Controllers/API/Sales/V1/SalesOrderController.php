<?php
namespace App\Http\Controllers\API\Sales\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Exceptions\MediAPIException;
use App\Models\Chemist;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Auth;
use App\Models\SfaSalesOrder;
use App\Models\SfaSalesOrderProduct;
use App\Models\SalesmanValidPart;
use App\Models\User;
use App\Ext\Get\SalesOrderHead;
use App\Ext\Get\SalesOrderHeadWrite;
use App\Ext\Get\SalesOrderLineWrite;
use App\Ext\Customer;
use App\Models\InvoiceLine;
use App\Models\UserAttendance;
use App\Models\SubTown;
use App\Traits\SalesTerritory;

class SalesOrderController extends  Controller{

    use SalesTerritory;

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

        // getContract
        $contract = SalesmanValidPart::where('u_id',$user->getKey())
            ->where('from_date','<=',$order_time)
            ->where('to_date','>=',$order_time)
            ->latest()->first();

        $contract = $contract?$contract->contract:NULL;

        // Getting the last attendance record related to user
        $data = [
            'u_id'=> $user->getKey(),
            'chemist_id'=> $json_decode['outletId'],
            'order_date'=> $order_time,
            'order_no'=>$json_decode['orderId']
        ];
        $ckSalesOrder = SfaSalesOrder::where($data)
                    ->latest()
                    ->first();

        if($ckSalesOrder){
            return [
                'result'=>true,
                "message" => "Sales order has been already added"
            ];
            // throw new MediAPIException('Sales order has been already added', 15);
        }else{
            try{
                DB::beginTransaction();
                $chemist = Chemist::with(['sub_town','sub_town.town','sub_town.town.area','sub_town.town.area.region'])->where('chemist_id',$json_decode['outletId'])->first();

                $salesOrder = SfaSalesOrder::create([
                    'order_no'=>$json_decode['orderId'],
                    'u_id'=>$user->getKey(),
                    'order_date'=>$order_time,
                    'order_mode'=>$json_decode['invoice_mode'],
                    'order_type'=>$json_decode['invoice_type'],
                    'chemist_id'=>$json_decode['outletId'],
                    'latitude'=>$json_decode['latitude'],
                    'longitude'=>$json_decode['longitude'],
                    'battery_lvl'=>$json_decode['batteryLevel'],
                    'app_version'=>$appVersion,
                    'contract'=>$contract
                ]);

                $order_id = $salesOrder->getKey();
                $order_amount = 0;
                foreach($json_decode['invoiceItems'] AS $pro){

                    $salesProduct = SfaSalesOrderProduct::create([
                        'order_id'=>$order_id,
                        'product_id'=>$pro['itemId'],
                        'sales_qty'=>$pro['qty'],
                        'price'=>$pro['price']
                    ]);

                    $order_amount += $pro['qty'] * $pro['price'];
                }
                $salesOrder->sales_order_amt = $order_amount;
                $salesOrder->sub_twn_id = $chemist->sub_twn_id;
                $salesOrder->ar_id = $chemist->sub_town->town->area->ar_id;
                $salesOrder->save();

                DB::commit();


                try {

                    $chemist = Chemist::with(['sub_town','sub_town.town','sub_town.town.area','sub_town.town.area.region'])->where('chemist_id',$json_decode['outletId'])->first();

                    $customer = Customer::where('customer_id',$chemist->chemist_code)->first();

                    SalesOrderHeadWrite::create([
                        'cash_register_id'=>"SFA",
                        'contract'=>$contract,
                        'sfa_order_no'=>$json_decode['orderId'],
                        'sfa_order_created_date'=>date('Y-m-d H:i:s'),
                        'sfa_order_sync_date'=>date('Y-m-d H:i:s'),
                        'order_date'=>$order_time,
                        'order_id'=>null,
                        'customer_no'=>$chemist->chemist_code,
                        'currency_code'=>null,
                        'wanted_delivery_date'=>date('Y-m-d H:i:s'),
                        'customer_po_no'=>null,
                        'salesman'=>$user->u_code,
                        'region_code'=>$customer?$customer->region:"DELETED",
                        'market_code'=>null,
                        'district_code'=>null,
                        'authorize_code'=>null,
                        'bill_addr_no'=>1,
                        'ship_addr_no'=>1,
                        'person_id'=>null,
                        'order_type'=>null,
                        'status'=>null,
                        'error_text'=>null
                    ]);

                    foreach($json_decode['invoiceItems'] AS $key=> $pro){
                        $product = Product::find($pro['itemId']);

                        SalesOrderLineWrite::create([
                            'sfa_order_no'=>$json_decode['orderId'],
                            'sfa_order_line_no'=>$key+1,
                            'catalog_no'=>$product->product_code,
                            'quantity'=>$pro['qty'],
                            'line_created_date'=>date("Y-m-d H:i:s"),
                            'status'=>"Created"
                        ]);
                    }

                    $salesOrder->integrated_at = date("Y-m-d H:i:s");
                    $salesOrder->save();

                    return [
                        'result'=>true,
                        "message" => "Sales order has been successfully added with no warnings."
                    ];

                } catch (\Exception $e1) {
                    throw $e1;
                }

                return [
                    'result'=>true,
                    "message" => "Sales order has been successfully added with no warnings."
                ];

            }catch(\Exception $e2){
                DB::rollback();
                throw $e2;
                throw new MediAPIException('Sales order has not been added', 16);
            }
        }
    }

    public function dailySaleOrders(Request $request){

        $user = Auth::user();

        $attendance = UserAttendance::where('u_id', $user->getKey())
                    ->latest()
                    ->first();

        if(!$attendance->checkout_status){
            $orders = SfaSalesOrder::with('salesOrderProducts','chemist')->where('u_id',$user->getKey())->whereDate('order_date','>=',date('Y-m-d',strtotime($attendance->check_in_time)))->whereDate('order_date','<=',date('Y-m-d',strtotime($attendance->check_in_time)))->get();
        }

        $orders->transform(function($order){

            $items = $order->salesOrderProducts->transform(function($val){
                return[
                    'item_id' => $val->product_id,
                    'qty' => $val->sales_qty,
                    'price' => $val->price,
                ];
            });

            $cusPriceGroup = Customer::where('customer_id',$order->chemist->chemist_code)->latest()->first();

            $subTownName = SubTown::where('sub_twn_id',$order->chemist->sub_twn_id)->first();
            return[
                'order_no' => $order->order_no,
                'u_id' => $order->u_id,
                'order_date' => $order->order_date,
                'order_no' => $order->order_no,
                'order_mode' => $order->order_mode,
                'order_type' => $order->order_type,
                'chemist_id' => $order->chemist_id,
                'latitude' => $order->latitude,
                'longitude' => $order->longitude,
                'battery_lvl' => $order->battery_lvl,
                'app_version' => $order->app_version,
                'contract' => $order->contract,
                'items' => $items,
                'chemist' => [
                    "chem_id"=> $order->chemist->chemist_id,
                    "chem_name"=> $order->chemist->chemist_name,
                    "chem_code"=> $order->chemist->chemist_code,
                    "chem_address"=> $order->chemist->chemist_address,
                    'chem_price_group'=>$cusPriceGroup?$cusPriceGroup->cust_price_grp:"",
                    'chem_price_list'=>$cusPriceGroup?$cusPriceGroup->sfa_price_list:"",
                    "chem_class_id"=> $order->chemist->chemist_class_id,
                    "chem_type_id"=> $order->chemist->chemist_type_id,
                    "chem_type"=> $order->chemist->chemist_type_name,
                    "chem_market_description_id"=> $order->chemist->chemist_mkd_id,
                    "chem_market_description" => $order->chemist->chemist_market_description,
                    "town_id"=> $order->chemist->sub_twn_id,
                    "town_name"=> $subTownName->sub_twn_name,
                    "route_id"=> $order->chemist->route_id,
                    "mobile_number"=> $order->chemist->mobile_number,
                    "phone_no"=> $order->chemist->phone_no,
                    "chemist_owner"=> $order->chemist->chemist_owner,
                    "credit_limit"=> $order->chemist->credit_limit,
                    "email"=> $order->chemist->email,
                    "lat"=> $order->chemist->lat,
                    "lon"=> $order->chemist->lon,
                    "updated_u_id"=> $order->chemist->updated_u_id,
                    "image_url"=> $order->chemist->image_url?url($order->chemist->image_url):""
                ]
            ];
        });

        return [
            'result'=>true,
            "orders" => $orders
        ];
    }

    public function getLastOrders(){
        $user = Auth::user();

        $getAllocatedTerritories = $this->getRoutesByItinerary($user);

        $outlets = Chemist::whereIn('route_id',$getAllocatedTerritories->pluck('route_id')->all())->get();
        $data = [];
        foreach ($outlets as $key => $outlet) {

            $query = DB::table('invoice_line as il');
            $query->select('il.invoice_no','il.product_id','il.chemist_id','il.invoice_date','il.sale_unit_price');
            $query->where('il.chemist_id',$outlet->chemist_id);
            $query->where('il.salesman_code','=',$user->u_code);
            $query->groupBy('il.invoice_no');
            $query->take(3);
            $query->orderBy('il.invoice_date', 'desc');
            $query->whereNull('il.deleted_at');
            $results = $query->get();

            $results->transform(function($val){
                $product = InvoiceLine::with('product')->where('invoice_no',$val->invoice_no)->where('chemist_id',$val->chemist_id)->get();

                    $value = DB::table('invoice_line AS il')
                            ->join('product AS p','il.product_id','=','p.product_id')
                            ->leftJoin('latest_price_informations AS pi',function($query){
                                $query->on('pi.product_id','=','p.product_id');
                                $query->on('pi.year','=',DB::raw('IF(MONTH(il.last_updated_on)<4,YEAR(il.last_updated_on)-1,YEAR(il.last_updated_on))'));
                                                            })
                            ->select([
                                'il.salesman_code',
                                DB::raw('ROUND(IFNULL(IF(pi.lpi_bdgt_sales="0.00",pi.lpi_pg01_sales,pi.lpi_bdgt_sales),0) * Ifnull(Sum(il.invoiced_qty), 0),2) AS amount'),
                                ])
                            ->where('il.chemist_id',$val->chemist_id)
                            ->where('il.invoice_no',$val->invoice_no)
                            ->whereNull('il.deleted_at')
                            ->first();

                    $product->transform(function($row){
                        return[
                            'productId' => $row->product_id,
                            'productName' => isset($row->product->product_name)?$row->product->product_name:"",
                            'qty' => $row->invoiced_qty,
                            'price' => $row->sale_unit_price
                        ];
                    });

                    return[
                        'invoiceNo' => $val->invoice_no,
                        'invoiceValue' => $value->amount,
                        'products' => $product,
                        'outstanding' => 0,
                        'outstandingStatus' => 0,
                        'invoiceDate' => $val->invoice_date,
                        'chemistId' => $val->chemist_id
                    ];
            });

            $data = array_merge($data,$results->toArray());
        }

        $orders = array_values(array_filter($data,function($item){
            return !!count($item);
        }));

        return[
            'result' => true,
            'invoices' => $orders
        ];
    }
}
