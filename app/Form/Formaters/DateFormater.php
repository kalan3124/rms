<?php
namespace App\Form\Formaters;

trait DateFormater {

    public function formatValue($name,$inst){

        $value = is_object($inst)?$inst->{$name}:$inst[$name];

        if(is_string($value)){
            return $value;
        } elseif(is_object($value)&&get_class($value) =='Illuminate\Support\Carbon') {
            return $value->format('Y-m-d');
        }
        return $value;
    }
}