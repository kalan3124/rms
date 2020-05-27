<?php
namespace App\Form\Inputs;

class TreeSelect extends Input{
    protected $type = 'tree_select';
    /**
     * Setting hierarchy 
     *
     * @param array $arr
     * @return self
     */
    public function setHierarchy($arr){
        $this->setCustomProp('hierarchy',$arr);
        return $this;
    }
    /**
     * Setting parent name
     * 
     * @param string $name
     * @return self
     */
    public function setParent($name){
        $this->setCustomProp('parent',$name);
        return $this;
    }
}