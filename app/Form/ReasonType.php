<?php
namespace App\Form;

use App\Form\Columns\ColumnController;

class ReasonType extends Form{

    protected $dropdownDesplayPattern = 'rsn_type';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('rsn_type')->setLabel('Reason');
    }
}