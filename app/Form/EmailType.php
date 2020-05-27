<?php
namespace App\Form;

class EmailType extends Form{

    protected $title='Email Type';

    protected $dropdownDesplayPattern = 'et_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('et_name')->setLabel('Name');
    }

}
