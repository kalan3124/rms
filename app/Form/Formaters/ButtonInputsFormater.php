<?php

namespace App\Form\Formaters;

trait ButtonInputsFormater {
    
    /**
     * Fetching the value before inserting it to database
     * 
     * @param mixed $value
     * @param array $other other input values
     * 
     * @return mixed
     */
    public function fetchValue($value,$other=[]){
        return $value;
    }
    /**
     * Format a row value
     * 
     * @param mixed $value
     * @param \App\Models\Base $inst
     * @return mixed
     */
    public function formatValue($name,$inst){
        return is_object($inst)?$inst->{$name}:$inst[$name];
    }
    /**
     * Render a value for pdf and csv
     *
     * @param mixed $value
     * @return mixed
     */
    public function render($value){
        return "";
    }
}