<?php
namespace App\Form;

    class DistributorCustomerSegment extends Form{

        protected $title='Distributor Customer Segment';

        protected $dropdownDesplayPattern = 'dcs_name';

        public function setInputs(\App\Form\Inputs\InputController $inputController)
        {
            $inputController->text('dcs_name')->setLabel('Distributor Customer Segment Name');
            $inputController->text('dcs_code')->setLabel('Distributor Customer Segment Code');
        }
    }
