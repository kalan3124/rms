<?php

namespace App\Form\Columns;

use App\Form\Formaters\CustomInputsFormater;


class Custom extends Column{

    use CustomInputsFormater;

    protected $type ="custom";

    protected $renderer=null;

    /**
     * Setting the component for the custom cell
     *
     * @param string $comp Custom Cell Component Name
     * @return static
     */
    public function setComponent($comp){
        $this->setCustomProp('component',$comp);
        return $this;
    }

    /**
     * Setting the renderer function
     *
     * @property callback $renderer
     * @return static
     */
    public function setRenderer($renderer){
        $this->renderer = $renderer;
        return $this;
    }

}