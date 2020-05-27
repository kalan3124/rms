<?php
namespace App\Form\Formaters;

trait AjaxDropdownFormater {

    public function formatValue($name,$inst){
        
        if(!$inst) return null;

        $value = is_object($inst)?$inst->{$name}:$inst[$name];

        $customProps = $this->input->getCustomProps();

        $link = $customProps['link'];

        $className = '\App\Form\\'.ucfirst(camel_case($link));

        $formModel = new $className();

        $pattern = $formModel->getDropdownDesplayPattern();

        $formatedPattern = \preg_replace('/([\w\.]+)/',$link.'.$1',$pattern);
        
        return [
            'value'=>$value,
            'label'=>replaceStringWithAssocArray(is_object($inst)?$inst->toArray():$inst,$formatedPattern)
        ];
    }

    public function render($value){
        if(!$value||!isset($value['label'])) return "";

        return $value['label'];
    }

    public function fetchValue($value,$other=[]){

        $multiple = false;

        if(isset($this->customProps['multiple'])&&$this->customProps['multiple']) $multiple=true;

        if($multiple)
            return $value;
        else
            return $value['value'];
    }  
}