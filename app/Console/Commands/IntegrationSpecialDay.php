<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\SpecialDay;

class IntegrationSpecialDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration:special_days';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loading special days from google calendar API';

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
        $this->info("Started the special day integration.");

        $instance = new \Google\Holidays();

        try{
            $proxy = config('shl.proxy_address');

            if($proxy){
                $proxy = str_replace('http://','tcp://',$proxy);

                stream_context_set_default(['http'=>['proxy'=>$proxy],'tcp'=>['proxy'=>$proxy]]);
            }

            $year = date('Y');
            $year++;

            $holidays = $instance->withApiKey('AIzaSyB7h1DuzatBpvp7qhL6NQBgJYFoMYa2ybQ')
                        ->inCountry('LK')
                        ->from($year.'-01-01')
                        ->to($year.'-12-31')
                        ->withMinimalOutput()
                        ->list();
            $holidayCount = count($holidays);

            $this->info("Fetched $holidayCount Holidays from Google API.");

            foreach ($holidays as $key => $holiday) {
                $specialDay = SpecialDay::firstOrCreate(['sd_date'=>$holiday['date']]);

                $specialDay->sd_name = $holiday['name'];

                $specialDay->save();

                $this->info("Updated {$holiday['name']} - {$holiday['date']}");
            }

        } catch (\Exception $exception){
            Storage::put('/public/errors/integration/'.date("Y-m-d").'-sd.txt',date("H:i:s")."\n".$exception->__toString()."\n\n");
            throw $exception;
        }

    }
}
