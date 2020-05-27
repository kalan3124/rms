<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\CSV\Base;
use Illuminate\Support\Facades\Storage;

class UploadCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * CSV Instance
     *
     * @var Base
     */
    protected $inst;
    /**
     * Create a new job instance.
     *
     * @param Base
     * @return void
     */
    public function __construct($csv)
    {
        $this->inst = $csv;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->inst->openFile();
        $formated = $this->inst->format();

        $inserted =($formated)? $this->inst->insert():false;

        if($inserted&&$formated)
            $this->inst->success();
    }

    public function failed($exception)
    {
        Storage::put('/public/errors/'.date("Y-m-d").'.txt',date("H:i:s")."\n".$exception->__toString()."\n\n");
        $this->inst->logStatus("error","Server error!",0);
    }
}
