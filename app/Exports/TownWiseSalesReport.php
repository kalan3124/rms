<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class TownWiseSalesReport implements FromView
{

    protected $data;
    
    protected $columns;

    public function __construct($data)
    {
        $this->data = $data;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view():View
    {
        return view('town-wise-sale-excel',$this->data);
    }
}
