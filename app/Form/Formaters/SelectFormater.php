<?php

namespace App\Form\Formaters;

trait SelectFormater {

    public function formatValue($name,$inst){

        $value = is_object($inst)?$inst->{$name}:$inst[$name];

        $customProps = $this->input->getCustomProps();

        $options = $customProps['options'];

        return [
            'value'=>$value,
            'label'=>isset($options[$value])?$options[$value]:"No Price List"
        ];
    }

    public function render($value){
        return $value['label'];
    }

    public function fetchValue($value,$other=[]){
        return $value['value'];
    }
}