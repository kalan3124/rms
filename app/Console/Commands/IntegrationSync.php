<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IntegrationSyncTime;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IntegrationSync extends Command
{
    protected $truncate = false;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration:sync {arg1?} {arg2?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncronizing data from their tables';

    /**
     * Model names in underscore notation
     */
    protected $models = [
        'customer',
        'regions',
        'salesman',
        'sales_part',
        'sales_price_list',
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

        $models = [];

        if($arg2=='truncate'||$arg1=='truncate'){
            $this->truncate= true; 
        }

        if($arg1&&$arg1!='truncate'){
            $models = explode(',',$arg1);
        } else {
            $models = $this->models;
        }

        foreach ($models as $name) {
            
                $this->syncTable($name);
                 
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

        if($hasPrimary&&!$this->truncate) $this->syncChanged($ourModel,$theirModel,$name);
        else if($this->truncate){
            $this->warn("Truncating table $name");
            $this->truncateAndInsert($ourModel,$theirModel,$name);
        }

        $this->info("Finished syncronizing table ext_".$name."_uiv at $time ");

    }

    protected function syncChanged($ourModel,$theirModel,$name){

        $primaryKey = $theirModel->getKeyName();
        $time = time();

        // Getting last timestamp
        $check_sync_time = IntegrationSyncTime::where('mysql_table_name',"ext_".$name."_uiv")
                ->first();

        if (!$check_sync_time) {

            $query = $theirModel::query();
        } else {
            if(!$check_sync_time->last_sync_status){
                $this->warn("Table is locked. Previous integration not completed yet.");
                return;
            }

            $check_sync_time->last_sync_status = 0;
            $check_sync_time->save();
            
            $query = $theirModel::where('last_updated_on', '>', $name =="invoice_line_delivery"? date('Y-m-d H:i:s',strtotime($check_sync_time->last_sync_time)-3*60) :$check_sync_time->last_sync_time);
        }

        
        try {

            $results = $query->get();

            $this->info("Fetched ".$results->count()." rows from oracle connection.");

            DB::beginTransaction();

                
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
           

            $last_updated = IntegrationSyncTime::firstOrNew([
                'mysql_table_name'=>'ext_'.$name.'_uiv'
            ]);
    
            $last_updated->last_sync_time = date('Y-m-d H:i:s',$time);
            $last_updated->last_sync_status = 1;
            $last_updated->save();

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();

            $this->error("Failed to write to $name table. Error:- ".$exception->__toString());
            $this->createIssueOnGitlab($name,$exception);
            Storage::put('/public/errors/integration/'.date("Y-m-d").'/'.$name.'.txt',date("H:i:s")."\n".$exception->__toString()."\n\n");
        }

    }

    protected function truncateAndInsert($ourModel,$theirModel,$name){
        $ourModel::truncate();

        $this->warn("Truncated table $name");

        $results = $theirModel::all();

        $this->info("Fetched ".$results->count()." rows from oracle connection.");

        if (!$results->isEmpty()) {

            foreach ($results as $key=> $row) {

                $data = [];

                foreach($ourModel->getFillable() as $name){
                    $data[$name] = $row->{$name};
                }
                
                if($key%1000==0) $this->info("Finished ".($key+1)." rows.");

                $exists = $ourModel::create($data);
            }

            $this->info("Changes affected to ".($key+1)." row(s).");
        
        }
    }

    protected function createIssueOnGitlab($name,\Exception $e){
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
            "title"=>"INTEGRATION ERROR [$name]",
            "description"=>"# Check the integration_sync_time table\nPlease change the value of last_sync_status column to 1 in $name row immediately.\n## Error Description \n```\n".$e->__toString()
                ."\n```\nError Time:- ".date('Y-m-d H:i:s')."\n\n @root @ramesh @chanaka @imalsha Please fix this.",
            "labels"=>'integration',
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
