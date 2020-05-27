<?php
namespace App\CSV;

use App\Models\User;
use App\Exceptions\WebAPIException;
use App\Models\DistributorBatch;
use App\Models\DistributorOpeningStock;
use App\Models\DistributorStock;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OpeningBalance extends Base {
    protected $title = "Opening Balance";

    protected $openingStock;

    protected $lastDisId = 0;

    protected $columns = [
        'dis_id'=>"Distributor Code",
        "product_id"=>"Product Code",
        "batch_id"=>"Batch Code",
        "qty"=>"Qty",
        "price"=>"Price",
        'tax_price'=>"Tax Price",
        'db_expire'=>'Expire (YYYY-MM-DD)'
    ];

    protected function formatValue($columnName, $value)
    {
        switch ($columnName) {
            case 'dis_id':
                if(!$value)
                    throw new WebAPIException("Please provide a distributor code!");
                $user = User::where((new User)->getCodeName(),"LIKE",$value)->first();
                if(!$user)
                    throw new WebAPIException("Distributor not found! Given distributor code is '$value'");
                return $user->getKey();
            case 'product_id':
                if(!$value)
                    throw new WebAPIException("Please provide a product code!");
                $product = Product::where((new Product)->getCodeName(),'LIKE',$value)->first();
                if(!$product)
                    throw new WebAPIException("Product not found! Given product code is '$value' ");
                return $product->getKey();
            case 'batch_id':
                if(!$value)
                    throw new WebAPIException("Please provide a batch code!");
                return $value;
            case 'db_expire':
                $time = strtotime($value);
                if(!$time)
                    throw new WebAPIException("Invali date supplied. Please provide and valid date.");
                return date('Y-m-d',$time);
            case 'qty':
                if(!$value||$value<=0)
                    throw new WebAPIException("Please provide a valid positive number to the qty");
                return (int) $value;
            case 'price':
                if(!$value||$value<=0)
                    throw new WebAPIException("Please provide a valid positive price.");

                return (float) $value;
            case 'tax_price':
                if(!$value||$value<=0)
                    throw new WebAPIException("Please provide a valid positive tax price.");

                return (float) $value;
            default:
                return (float) ($value<=0||!$value)?null:$value;
        }
    }

    protected function insertRow($row)
    {
        /** @var DistributorBatch $batch */
        $batch = DistributorBatch::firstOrCreate([
            'db_code'=>$row['batch_id'],
            'product_id'=>$row['product_id'],
        ]);
        

        $batch->db_expire = $row['db_expire'];
        $batch->db_price = $row['price'];
        $batch->db_tax_price = $row['tax_price'];
        $batch->save();


        if($this->lastDisId!==$row['dis_id']){

            $existOpeningStock = DistributorOpeningStock::where('dis_id',$row['dis_id'])->first();
            
            $this->openingStock = DistributorOpeningStock::create(['dis_id'=>$row['dis_id']]);

            if($existOpeningStock){

                $stocks =  DB::table('distributor_stock AS ds')
                    ->join('distributor_batches AS db','db.db_id','=','ds.db_id')
                    ->select([DB::raw('(SUM(ds.ds_credit_qty) - SUM(ds.ds_debit_qty) ) AS stock'),'db.db_id','db.db_price','db.product_id'])
                    ->where('ds.dis_id',$row['dis_id'])
                    ->whereDate('db.db_expire','>=',date('Y-m-d'))
                    ->groupBy('db.db_id')
                    ->orderBy('db.db_expire')
                    ->having('stock','>','0')
                    ->get();

                foreach ($stocks as $key => $stock) {
                    DistributorStock::create([
                        'ds_ref_type'=>5,
                        'ds_ref_id'=>$this->openingStock->getKey(),
                        'dis_id'=>$row['dis_id'],
                        'product_id'=>$stock->product_id,
                        'db_id'=>$stock->db_id,
                        'ds_credit_qty'=>0,
                        'ds_debit_qty'=>$stock->stock
                    ]);
                }
            }
        }

        DistributorStock::create([
            'ds_ref_type'=>5,
            'ds_ref_id'=>$this->openingStock->getKey(),
            'dis_id'=>$row['dis_id'],
            'product_id'=>$row['product_id'],
            'db_id'=>$batch->getKey(),
            'ds_credit_qty'=>$row['qty'],
            'ds_debit_qty'=>0
        ]);

        $this->lastDisId = $row['dis_id'];
    }

}