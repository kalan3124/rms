<?php
namespace App\Form;

    class DistributorCustomerClass extends Form{

        protected $title='Distributor Customer Class';

        protected $dropdownDesplayPattern = 'dcc_name';

        public function setInputs(\App\Form\Inputs\InputController $inputController)
        {
            $inputController->text('dcc_name')->setLabel('Distributor Customer Name');
            $inputController->text('dcc_code')->setLabel('Distributor Customer Code');
        }
    }
