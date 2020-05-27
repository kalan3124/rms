<?php
namespace App\Form;

class VehicleType extends Form{

    protected $title='Vehicle Type';

    protected $dropdownDesplayPattern = 'vht_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('vht_name')->setLabel('Vehicle Type Name');
        $inputController->text('vht_code')->setLabel('Vehicle Type Code');
    }

}