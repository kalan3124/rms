<?php
namespace App\Form;

class VehicleTypeRate extends Form{

    protected $title='Vehicle Type Rate';

    protected $dropdownDesplayPattern = '';

    public function beforeSearch($query, $values)
    {
        $query->with(['vehicle_type','user_type']);
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->ajax_dropdown('vht_id')->setLink('vehicle_type')->setLabel('Vehicle Type');
        $inputController->ajax_dropdown('u_tp_id')->setLink('user_type')->setLabel('User Type');
        $inputController->number('vhtr_rate')->setLabel('Rate');
        $inputController->date('vhtr_srt_date')->setLabel("Starting Date");

        $inputController->setStructure([
            ['vht_id','u_tp_id'],
            ['vhtr_rate','vhtr_srt_date']
        ]);
    }

}