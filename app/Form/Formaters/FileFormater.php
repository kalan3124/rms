<?php

namespace App\Form\Formaters;

trait FileFormater {

    public function formatValue($name,$inst){

        $value = is_object($inst)?$inst->{$name}:$inst[$name];

        $customProps = $this->input->getCustomProps();

        $type = $customProps['file_type'];


        return url('/storage/'.$type.'/'.$value);
    }
}