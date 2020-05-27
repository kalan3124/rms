<?php 
namespace App\Form;

Class HospitalStaffCategory Extends Form {

    protected $title='Hospital Staff Categories';

    protected $dropdownDesplayPattern = 'hos_stf_cat_name';


    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('hos_stf_cat_name')->setLabel('Hospital Staff Category Name');
        $inputController->text('hos_stf_cat_short_name')->setLabel('Hospital Staff Category Short Name');
    }
}