<?php
namespace App\Form\Columns;

class Link extends Column {

    protected $searchable = false;

    public function render($value){
        return isset($value)? $value['link']: "";
    }

    protected $type ="link";

    public function setLink($link){
        $this->setCustomProp('link',$link);
        return $this;
    }

    public function setDisplayLabel($label){
        $this->setCustomProp("label",$label);
        return $this;
    }

}