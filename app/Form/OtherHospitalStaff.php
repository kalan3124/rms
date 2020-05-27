<?php 
namespace App\Form;

Class OtherHospitalStaff Extends Form {

    protected $title='Other Hospital Staff';

    protected $dropdownDesplayPattern = 'hos_stf_name';

    public function beforeDropdownSearch($query,$keyword){
        $query->with('hospital_staff_category');
    }
    public function beforeSearch($query,$values){
        $query->with('hospital_staff_category');
        $query->with('institution','institution.institution_category');
        $query->with('sub_town','sub_town.town');
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('hos_stf_name')->setLabel('Name');
        $inputController->text('hos_stf_code')->setLabel('Code');
        $options = [
            1=>'Male',
            2=>'Female'
        ];

        $inputController->select('gender')->setLabel('Gender')->setOptions($options);
        $inputController->text('date_of_birth')->setLabel('Date Of Birth');
        $inputController->text('phone_no')->setLabel('Phone Number');
        $inputController->text('mobile_no')->setLabel('Mobile Number');
        $inputController->ajax_dropdown('hos_stf_cat_id')->setLabel('Hospital Staff Category')->setLink('hospital_staff_category');
        $inputController->ajax_dropdown('sub_twn_id')->setLabel('Sub Town')->setLink('sub_town');
        $inputController->ajax_dropdown('ins_id')->setLabel('Institution')->setLink('institution');

        $inputController->setStructure([
          ['hos_stf_name','hos_stf_code'],
          ['date_of_birth','gender'],
          ['phone_no','mobile_no'],
          'hos_stf_cat_id',
          ['sub_twn_id','ins_id']
        ]);
    }
}