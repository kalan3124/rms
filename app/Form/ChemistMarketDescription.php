<?php 
namespace App\Form;

class ChemistMarketDescription extends Form {

    protected $title = 'Chemist Market Description';

    protected $dropdownDesplayPattern = 'chemist_mkd_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('chemist_mkd_name')->setLabel('Chemist Market Description');
        $inputController->text('chemist_mkd_code')->setLabel('Chemist Market Description Code')->isUpperCase();
    }
}