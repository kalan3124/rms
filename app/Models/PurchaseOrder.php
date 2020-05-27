<?php

namespace App\Models;

use App\Exceptions\DisAPIException;
use App\Ext\Customer;
use App\Ext\Get\SalesOrderHeadWrite;
use App\Ext\Get\SalesOrderLineWrite;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Purchase Order Model for distributors
 * 
 * @property int $po_id Primary Key
 * @property int $dis_id Distributor Id
 * @property int $created_u_id Created user of the distributor
 * @property string $po_number
 * @property float $po_amount
 * @property string $integrated_at
 * @property int $sr_id Sales Representative Id (Not Useful)
 * @property int $site_id
 * 
 * @property User $createdUser
 * @property User $distributor
 * @property User $salesRep
 * @property Collection|PurchaseOrderLine[] $lines
 * @property Site $site
 */
class PurchaseOrder extends Base {

    protected $table = 'purchase_order';

    protected $primaryKey = 'po_id';

    protected $fillable = [
        'dis_id',
        'created_u_id',
        'po_number',
        'po_amount',
        'integrated_at',
        'sr_id',
        'site_id',
        'po_confirm_at'
    ];

    public function createdUser(){
        return $this->belongsTo(User::class,'created_u_id','id');
    }

    public function distributor(){
        return $this->belongsTo(User::class,'dis_id','id');
    }

    public function salesRep(){
        return $this->belongsTo(User::class,'sr_id','id');
    }

    public function lines(){
        return $this->hasMany(PurchaseOrderLine::class,'po_id','po_id');
    }

    public function site(){
        return $this->belongsTo(Site::class,'site_id','site_id');
    }

    /**
     * Trying to integrating the purchase order to IFS
     *
     * @return boolean
     */
    public function sendToIFS(){
        /** @var self $po */
        $po = self::with(['lines','lines.product','salesRep','distributor','site'])->find($this->getKey());

        if(!$po->site)
            throw new DisAPIException("Can not find a site to distributor");


        $customer = Customer::where('customer_id',$po->distributor->u_code)->first();
        
        
        try {

            DB::connection('oracle')->beginTransaction();

            SalesOrderHeadWrite::create([
                'cash_register_id'=>"DIS",
                'contract'=>$po->site->site_code,
                'sfa_order_no'=>$po->po_number,
                'sfa_order_created_date'=>date('Y-m-d H:i:s'),
                'sfa_order_sync_date'=>date('Y-m-d H:i:s'),
                'order_date'=>$po->created_at->format('Y-m-d H:i:s'),
                'order_id'=>null,
                'customer_no'=>$po->distributor->u_code,
                'currency_code'=>null,
                'wanted_delivery_date'=>date('Y-m-d H:i:s'),
                'customer_po_no'=>null,
                'salesman'=>$po->salesRep->u_code,
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
                
            foreach($po->lines AS $key=> $line){
                $product = $line->product;

                SalesOrderLineWrite::create([
                    'sfa_order_no'=>$po->po_number,
                    'sfa_order_line_no'=>$key+1,
                    'catalog_no'=>$product->product_code,
                    'quantity'=>$line->pol_qty,
                    'line_created_date'=>date("Y-m-d H:i:s"),
                    'status'=>"Created"
                ]);
            }

            $po->integrated_at = date("Y-m-d H:i:s");
            $po->save();

            DB::connection('oracle')->commit();

            return true;
        } catch (\Exception $e1) {
            DB::connection('oracle')->rollBack();
            Storage::put('/public/errors/po/'.date('Y-m-d').'.txt',date('Y-m-d H:i:s').'\n\n'.$e1->__toString().'\n'.json_encode($this->getKey()));

            $this->createIssueOnGitlab($po->po_number,$e1);

            return false;
        }

        return false;
    }

    public static function generateNumber($disId){
        $purchaseOrderCount = self::where('dis_id',$disId)->count();

        $distributor = User::where('id',$disId)->first();
        
        return 'PO/'.$distributor->u_code.'/'.str_pad($purchaseOrderCount +1,8);
    }


    protected function createIssueOnGitlab($poNumber,\Exception $e){
        $mainConfig = [];
        
        if(config("shl.proxy_address")){
            $mainConfig['proxy'] = config("shl.proxy_address");
        }

        $client = new Client($mainConfig);

        $projectId = config("gitlab.project_id");

        $formatedType = RequestOptions::JSON;

        $options = [
            'headers' => [
                'PRIVATE-TOKEN'=> config("gitlab.access_token")
            ]
        ];

        $method = "POST";

        $data = [
            "title"=>"PO ERROR [$poNumber]",
            "description"=>"# PO has not integrated to IFS app\nPlease clone the $poNumber and integrate it immedietly.\n## Error Description \n```\n".$e->__toString()
                ."\n```\nError Time:- ".date('Y-m-d H:i:s')."\n\n @root @ramesh @chanaka @imalsha @hashini Please fix this ASAP.",
            "labels"=>'purchase_order',
            "assignee_ids"=>config("gitlab.current_devs")
        ];

        if($method=="POST"){
            $options[$formatedType]=$data;
            $options['headers']['Content-Type'] = 'application/json';
            $options['headers']['Accept']= 'application/json';
        }

        try{
            $response = $client->request($method,config("gitlab.server")."/projects/$projectId/issues",$options);
        } catch (\Exception $e ){
        }
    }
}