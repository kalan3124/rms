<?php
namespace App\Form;

class Institution extends Form{

    protected $title='Institution';

    protected $dropdownDesplayPattern = 'ins_name - institution_category.ins_cat_name';


    public function beforeDropdownSearch($query,$keyword){
        $query->with('institution_category');
    }

    public function beforeSearch($query,$values){
        $query->with('institution_category','sub_town','sub_town.town');
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('ins_name')->setLabel('Institution Name');
        $inputController->text('ins_short_name')->setLabel('Short Name');
        $inputController->text('ins_code')->setLabel('Code')->isUpperCase();
        $inputController->text('ins_address')->setLabel('Address');
        $inputController->ajax_dropdown('ins_cat_id')->setLabel('Category')->setLink('institution_category');
        // $inputController->ajax_dropdown('twn_id')->setLabel('Town')->setLink('town');
        $inputController->ajax_dropdown('sub_twn_id')->setLabel('Sub Town')->setlink('sub_town');

        $inputController->setStructure([
            'ins_name',
            ['ins_short_name','ins_code'],
            ['ins_cat_id'],
            ['sub_twn_id','ins_address']
        ]);

    }

}