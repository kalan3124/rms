<?php
namespace App\Form;

use Illuminate\Support\Facades\Auth;


class Competitors extends Form{

    protected $title='Competitor';


    protected $dropdownDesplayPattern = 'cmp_name';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('cmp_name')->setLabel('Competitor Name');
        $inputController->text('cmp_address')->setLabel('Competitor Address');
    }
}
