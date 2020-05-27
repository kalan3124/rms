<?php

namespace App\Form\Columns;

class Number extends Text{

    protected $type='number';

    protected $searchable = false;

    public function getSearchCondition($value){
        return [$this->name,'=',$value];
    }
}