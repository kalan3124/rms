<?php 
namespace App\Form;

    class ChemistTypes extends Form{

        protected $title='Chemist Type';

        protected $dropdownDesplayPattern = 'chemist_type_name';

        public function setInputs(\App\Form\Inputs\InputController $inputController)
        {
            $inputController->text('chemist_type_name')->setLabel('Chemist Type Name');
            $inputController->text('chemist_type_short_name')->setLabel('Chemist Type Short Name');
        }
    }