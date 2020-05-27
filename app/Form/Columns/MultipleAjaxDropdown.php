<?php
namespace App\Form\Columns;

class MultipleAjaxDropdown extends AjaxDropdown{

    protected $type = 'multiple_ajax_dropdown';

    protected $searchable = false;

    public function render($value){
        $string = "";
        foreach($value as $key=> $val){
            if($key!=0)
                $string.=',';
            $string.=$val['label'];
        }

        return $string;
    }

}