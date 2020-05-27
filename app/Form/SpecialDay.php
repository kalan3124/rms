<?php
namespace App\Form;

class SpecialDay Extends Territory 
{
    protected $title='Special Day';

    protected $dropdownDesplayPattern = 'sd_name - sd_date';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->date('sd_date')->setLabel('Date');
        $inputController->text('sd_name')->setLabel('Day Name');
    }

}