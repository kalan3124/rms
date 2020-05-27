<?php

namespace App\Form\Columns;

class Text extends Column{

    protected $type='text';

    public function getSearchCondition($value){
        return [$this->name,'LIKE','%'.$value.'%'];
    }
}