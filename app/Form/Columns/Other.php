<?php

namespace App\Form\Columns;

use App\Form\Formaters\OtherInputsFormater;

class Other extends Column{
    use OtherInputsFormater;
    
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