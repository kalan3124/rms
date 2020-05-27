<?php 
namespace App\Form;

class VisitType extends Form{

    protected $title = 'Visit Type';

    public $dropdownDesplayPattern = "vt_name";

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('vt_name')->setLabel('Visit Type');
    }
}