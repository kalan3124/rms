<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IntegrationBackward extends IntegrationDateRange
{

    protected $fromDate ;

    protected $toDate;

    protected $fileName = '/public/logs/backward_integration.txt';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration:backward {arg1} {arg2?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This special command is developed to fetch previous year sales data.';

    /**
     * Table names in underscore notation
     */
    protected $models = [
        'customer',
        'regions',
        'salesman',
        'sales_price_list',
        'sales_part',
        'site',
        'tax_codes',
        'invoice_head',
        'invoice_lines',
        'return_lines',
        'salesman_valid_cust',
        'salesman_valid_parts',
        'invent_part_in_stock'
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

        if($arg2){
            $models = explode(',',$arg2);
        } else {
            $models = $this->models;
        }

        if(Storage::exists($this->fileName)){
            $toTimestamp = Storage::get($this->fileName);

            if($toTimestamp=='locked'){
                $this->error("Locked the command. There is a running proccess with the same command. Please stop it and try again. ");
                return;
            }

            $this->toDate = date('Y-m-d',(int)$toTimestamp);
        } else {
            $this->toDate = date('Y-m-d');
        }

        Storage::put($this->fileName,'locked');

        $this->fromDate = date('Y-m-d',strtotime($this->toDate.' -'.$arg1.' days'));

        $this->info('Integration started from '.$this->fromDate.' to '.$this->toDate);


        foreach ($models as $name) {
            try {
                DB::beginTransaction();

                    
                $this->syncTable($name);
                    

                DB::commit();
            } catch (\Exception $exception) {
                DB::rollback();

                $this->error("Failed to write to $name table. Error:- ".$exception->__toString());

                Storage::put('/public/errors/integration/'.date("Y-m-d").'/'.$name.'.txt',date("H:i:s")."\n".$exception->__toString()."\n\n");
            }
        }

        Storage::put( $this->fileName,strtotime( $this->fromDate));
    }

}
