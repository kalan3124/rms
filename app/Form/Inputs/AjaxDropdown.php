<?php
namespace App\Form\Inputs;

use App\Form\Formaters\AjaxDropdownFormater;


class AjaxDropdown extends Input{

    use AjaxDropdownFormater;
    
    protected $type = 'ajax_dropdown';

    /**
     * Set the link to load items
     *
     * @param string $link Form name in underscore notation
     * @return static
     */
    public function setLink(string $link){
        $this->setCustomProp('link',$link);
        return $this;
    }

    /**
     * Weather that input showing limited options for user
     *
     * @param boolean $lim
     * We are limiting ajax dropdown options for 30 by default. You can turn off the limitation by passing false
     * 
     * @return static
     */
    public function setLimit($lim=false){
        $this->setCustomProp('limit',$lim);
        return $this;
    }

    /**
     * Setting the filteration options
     *
     * @param array $where 
     * Supply a associated array to $where parameter. Use '{other_column_name}' syntax to filter based on other input value.
     * 
     * <code>
     *  [
     *      'chemist_id'=>2,
     *      'chemist_class'=>'{chemist_class_id}'
     *  ]
     * </code>
     * @return static
     */
    public function setWhere($where=[]){
        $this->setCustomProp('where',$where);
        return $this;
    }

    /**
     * Enable the multiple mode
     *
     * @param boolean $multi 
     * @return static
     */
    public function setMultiple($multi=true){
        $this->setCustomProp('multiple',$multi);
        if($multi) $this->setType('multiple_ajax_dropdown');
        return $this;
    }

}