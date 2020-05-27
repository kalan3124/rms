<?php
namespace App\Form;

class DoctorClass extends Form{

    protected $title='Doctor Classes';

    protected $dropdownDesplayPattern = 'doc_class_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('doc_class_name')->setLabel('Class Name');
    }

}