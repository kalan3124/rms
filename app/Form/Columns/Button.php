<?php

namespace App\Form\Columns;

use App\Form\Formaters\ButtonInputsFormater;


class Button extends Column{

    use ButtonInputsFormater;

    protected $searchable = false;

    protected $type ="button";

    public function setLink($link){
        $this->setCustomProp('link',$link);
        return $this;
    }

    public function setLabel($label){
        $this->setCustomProp("label",$label);
        return $this;
    }

}