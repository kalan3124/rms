<?php

namespace App\Console\Commands;

use App\Models\Chemist;
use App\Models\SfaSalesOrder;
use App\Models\User;
use App\Ext\Customer;
use App\Ext\Get\SalesOrderHeadWrite;
use App\Ext\Get\SalesOrderLineWrite;
use App\Models\Product;
use Illuminate\Console\Command;

class IntegrationSalesOrders extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration:sales_orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending failed sales order data to their server.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Starting the integrations.");

        $failedOrders = SfaSalesOrder::with('salesOrderProducts')->whereNull('integrated_at')->whereDate('order_date','>=','2019-11-26')->get();

        $this->info("Found ".$failedOrders->count().' orders to sync.');

        $failed = 0;
        $success = 0;

        foreach ($failedOrders as $key => $failedOrder) {

            try {
                $user = User::find($failedOrder->u_id);
                $chemist = Chemist::with(['sub_town','sub_town.town','sub_town.town.area','sub_town.town.area.region'])->where('chemist_id',$failedOrder->chemist_id)->first();
                $customer = Customer::where('customer_id',$chemist->chemist_code)->first();
                SalesOrderHeadWrite::create([
                    'contract'=>$failedOrder->contract,
                    'cash_register_id'=>"SFA",
                    'sfa_order_no'=>$failedOrder->order_no,
                    'sfa_order_created_date'=>$failedOrder->created_at->format('Y-m-d H:i:s'),
                    'sfa_order_sync_date'=>date('Y-m-d H:i:s'),
                    'order_date'=>$failedOrder->created_at->format('Y-m-d H:i:s'),
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
    
                foreach($failedOrder->salesOrderProducts AS $key=> $salesOrderProduct){
                    $product = Product::find($salesOrderProduct->product_id);
    
                    SalesOrderLineWrite::create([
                        'sfa_order_no'=>$failedOrder->order_no,
                        'sfa_order_line_no'=>$key+1,
                        'catalog_no'=>$product->product_code,
                        'quantity'=>$salesOrderProduct->sales_qty,
                        'line_created_date'=>date("Y-m-d H:i:s"),
                        'status'=>"Created"
                    ]);
                }
    
                $failedOrder->integrated_at = date("Y-m-d H:i:s");
                $failedOrder->save();
                $this->info('[+]'.$failedOrder->order_no);
                $success++;
            } catch (\Exception $e){
                $this->warn('[-]'.$failedOrder->order_no);
                $failed++;
            }

            $this->info("Finished integrating sales orders. Success:- $success , Failed:- $failed") ;   

        }
    }

}
