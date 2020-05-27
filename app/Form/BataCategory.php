<?php
namespace App\Form;

class BataCategory extends Form{

    protected $title='Bata Category';

    protected $dropdownDesplayPattern = 'btc_category';


    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('btc_category')->setLabel('Category Name');
        $inputController->text('btc_code')->setLabel('Category Code');
    }

}