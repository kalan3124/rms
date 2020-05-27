<?php
namespace App\Form\Inputs;

class Text extends Input{
    protected $type = 'text';

    public function isUpperCase($status=true){
        $this->setCustomProp('upper_case',$status);
        return $this;
    }
}