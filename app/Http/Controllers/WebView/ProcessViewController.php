<?php

namespace App\Http\Controllers\WebView;

use App\Http\Controllers\Controller;

class ProcessViewController extends Controller {
    public function view(){
        exec('ps -aux', $processes);
        foreach($processes as $process)
        {
            $cols = split(' ', ereg_replace(' +', ' ', $process));
            if (strpos($cols[2], '.') > -1)
            {
                $cpuUsage += floatval($cols[2]);
            }
        }
        print($cpuUsage);
    }
}