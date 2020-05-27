<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class ExportDistributorReturns implements FromView
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function view(): View
    {
        return view('distributor_returns',$this->data);
    }
}
