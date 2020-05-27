<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IntegrationSyncTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IntegrationDateRange extends Command
{

    protected $fromDate ;

    protected $toDate;

    /**
     * The name and signature of the console command.
     * 
     * 
     * Usage:-
     * ```
     * $ php artisan integration:date_range 2019-01-01 2019-02-01
     * $ php artisan integration:date_range 2019-01-01 2019-02-01 invoice_line_delivery
     * ```
     *
     * @var string
     */
    protected $signature = 'integration:date_range {arg1} {arg2?} {arg3?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncronizing data from their tables for a date range';

    /**
     * Table names in underscore notation
     */
    protected $models = [
        'customer',
        'regions',
        'salesman',
        // 'sales_price_list',
        'sales_part',
        'site',
        'tax_codes',
        'invoice_head',
        'invoice_lines',
        'return_lines',
        'salesman_valid_cust',
        'salesman_valid_parts',
        'invent_part_in_stock',
        'invoice_line_delivery',
    ];
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

        $arg1 = $this->argument('arg1');
        $arg2 = $this->argument('arg2');
        $arg3 = $this->argument('arg3');

        $this->fromDate = $arg1;
        if($arg2){
            $this->toDate = $arg2;
        } else {
            $this->toDate = date('Y-m-d H:i:s');
        }

        $models = [];

        if($arg3){
            $models = explode(',',$arg3);
        } else {
            $models = $this->models;
        }

        foreach ($models as $name) {
            try {


                $sync_time = IntegrationSyncTime::firstOrCreate([
                            'mysql_table_name'=>"ext_".$name."_uiv"
                        ]);
                /** @var IntegrationSyncTime $sync_time */
                $sync_time->last_sync_status = 0;
                $sync_time->save();

                DB::beginTransaction();
                    
                $this->syncTable($name);

                DB::commit();

                $sync_time->last_sync_status = 1;
                $sync_time->save();
                    

            } catch (\Exception $exception) {
                DB::rollback();

                $this->error("Failed to write to $name table. Error:- ".$exception->__toString());

                Storage::put('/public/errors/integration/'.date("Y-m-d").'/'.$name.'.txt',date("H:i:s")."\n".$exception->__toString()."\n\n");
            }
        }
    }

    protected function syncTable($name){
        $time = date('Y-m-d H:i:s.u');
        $this->info("Syncronizing table ext_".$name."_uiv at $time ");

        $className = ucfirst(camel_case($name));

        $ourModelName = '\App\Ext\\'.$className;
        $theirModelName = '\App\Ext\Get\\'.$className;

        $ourModel = new $ourModelName;
        $theirModel = new $theirModelName;

        $hasPrimary = $theirModel->hasPrimary;

        if($hasPrimary){
            $this->syncChanged($ourModel,$theirModel,$name);
        }

        $this->info("Finished syncronizing table ext_".$name."_uiv at $time ");

    }

    protected function syncChanged($ourModel,$theirModel,$name){

        $primaryKey = $theirModel->getKeyName();

        $results = $theirModel::whereDate('last_updated_on', '>=', $this->fromDate)
            ->whereDate('last_updated_on','<=',$this->toDate)
            ->get();

        $this->info("Fetched ".$results->count()." rows from oracle connection.");
        
        if (!$results->isEmpty()) {

            $updated = 0;

            foreach ($results as $key=> $row) {

                if($theirModel->hasCompositePrimary){
                    $primary = $theirModel->getKeyName();

                    $where = [];

                    foreach($primary as $column){
                        $where[$column] = $row->{$column};
                    }

                    $exists = $ourModel::where($where)->latest()->first();
                }
                else
                    $exists = $ourModel::where($primaryKey, $row->{$primaryKey})->latest()->first();
        
                $data = [];

                foreach($ourModel->getFillable() as $columnName){
                    $data[$columnName] = $row->{$columnName};
                }

                if($key%1000==0) $this->info("Finished ".($key+1)." rows.");
        
                if ($exists) {
                    $updated++;
                    
                    if($theirModel->hasCompositePrimary){
                        $primary = $theirModel->getKeyName();
    
                        $where = [];
    
                        foreach($primary as $column){
                            $where[$column] = $row->{$column};
                        }
    
                        $exists = $ourModel::where($where)->update($data);
                    }
                    else{
                        $exists->update($data);
                    }

                    $theirModel->afterUpdate($exists,$data);
                } else {
                    $exists = $ourModel::create($data);
                    $theirModel->afterCreate($exists,$data);
                }

            }

            $this->info("Changes affected to ".($key+1)." row(s). ".($updated)." rows updated. ".($key+1-$updated)." rows newly created");
        }
        
    }

}
