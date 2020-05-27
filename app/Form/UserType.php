<?php
namespace App\Form;

class UserType extends Form{

    protected $title='User Type';

    protected $dropdownDesplayPattern = 'user_type';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('user_type')->setLabel('User Type Name');

        $options = [
            1=>'FFA',
            2=>'SFA'
        ];

        $inputController->select('main_user_type')->setLabel('Main User Type')->setOptions($options);
    }


}
