<?php
namespace App\Form;

class InstitutionCategory extends Form{

    protected $title='Institution Categories';

    protected $dropdownDesplayPattern = 'ins_cat_name';


    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('ins_cat_name')->setLabel('Category Name');
        $inputController->text('ins_cat_short_name')->setLabel('Category Short Name');
    }

}