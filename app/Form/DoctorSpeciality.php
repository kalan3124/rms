<?php
namespace App\Form;

class DoctorSpeciality extends Form{

    protected $title='Doctor Specialities';

    protected $dropdownDesplayPattern = 'speciality_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('speciality_name')->setLabel('Speciality Name');
        $inputController->text('speciality_short_name')->setLabel('Speciality Short Name');
    }

}