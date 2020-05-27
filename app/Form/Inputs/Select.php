<?php
namespace App\Form\Inputs;

use App\Form\Formaters\SelectFormater;


class Select extends Input{

    use SelectFormater;

    protected $type = 'select';

    public function setOptions(array $options){
        $this->setCustomProp('options',$options);
        return $this;
    }
}