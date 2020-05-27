<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GPSTableCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:new_table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renamiing current gps table to another name and create new gps table.';

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
        Schema::rename('gps_tracking', 'gps_tracking_' .date('Y_m', strtotime(date('Y-m')." -1 month")));

        Schema::create('gps_tracking', function (Blueprint $table) {
            $table->increments('gt_id');

            $table->unsignedInteger('u_id')->nullable();
            $table->foreign('u_id','gps_tracking_users_'.date('Y_m'))->references('id')->on('users');

            $table->decimal('gt_lon', 10, 7);
            $table->decimal('gt_lat', 10, 7);

            $table->tinyInteger('gt_btry');
            $table->decimal('gt_speed', 10, 8);

            $table->timestamp('gt_time');

            $table->decimal('gt_brng', 10, 4);

            $table->decimal('gt_accu', 10, 4);

            $table->tinyInteger('gt_prvdr')->comment('0-GPS,1-Network,2-Undefind');

            $table->softDeletes();
            $table->timestamps();
        });

    }
}
