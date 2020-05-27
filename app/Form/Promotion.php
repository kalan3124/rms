<?php 
namespace App\Form;

class Promotion extends Form {

    protected $title='Promotion';

    protected $dropdownDesplayPattern = 'promo_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('promo_name')->setLabel('Promotion Name');
        $inputController->date('start_date')->setLabel('Start Date');
        $inputController->date('end_date')->setLabel('End Date');
    }
}