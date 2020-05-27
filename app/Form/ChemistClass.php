<?php 
namespace App\Form;

class ChemistClass extends Form {

    protected $title='Chemist Class';

    protected $dropdownDesplayPattern = 'chemist_class_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('chemist_class_name')->setLabel('Chemist Class Name');
    }
}

