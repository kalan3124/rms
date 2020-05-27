<?php
namespace App\Form;

class Reason extends Form{

    protected $title='Reason';

    protected $dropdownDesplayPattern = 'rsn_name';

    public function beforeDropdownSearch($query,$keyword){
        $query->with('reason_type');
    }

    public function beforeSearch($query,$values){
        $query->with('reason_type');
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('rsn_name')->setLabel('Reason');

        $inputController->ajax_dropdown('rsn_type')->setLabel('Reason Type')->setLink('reason_type');
    }

}