<?php 
namespace App\Form;

class Chemist Extends Form{

    protected $title = 'Chemist';

    protected $dropdownDesplayPattern = 'chemist_name';

    public function beforeSearch($query,$values){
        $query->with('chemist_class','chemist_types','chemist_market_description','sub_town','sub_town.town','route');
    }

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('chemist_name')->setLabel('Chemist Name');
        $inputController->text('chemist_code')->setLabel('Chemist Code')->isUpperCase();
        $inputController->text('chemist_address')->setLabel('Chemist Address');
        $inputController->text('telephone')->setLabel('Telephone Number');
        $inputController->text('credit_amount')->setLabel('Credit Amount');
        $inputController->ajax_dropdown('sub_twn_id')->setLabel('Sub Town')->setLink('sub_town');
        $inputController->ajax_dropdown('route_id')->setLabel('Route')->setLink('route');
        $inputController->ajax_dropdown('chemist_class_id')->setLabel('Chemist Class')->setLink('chemist_class');
        $inputController->ajax_dropdown('chemist_type_id')->setLabel('Chemist Type')->setLink('chemist_types');
        $inputController->ajax_dropdown('chemist_mkd_id')->setLabel('Chemist Market Description')->setLink('chemist_market_description');
        $inputController->text('mobile_number')->setLabel('Mobile Number');
        $inputController->text('phone_no')->setLabel('Phone Number');
        $inputController->text('chemist_owner')->setLabel('Chemist Owner');
        $inputController->text('credit_limit')->setLabel('Credit Limit');
        $inputController->text('email')->setLabel('Email');
        $inputController->text('lat')->setLabel('Lat');
        $inputController->text('lon')->setLabel('Lon');
        $inputController->text('updated_u_id')->setLabel('Updated User');
        $inputController->image('image_url')->setLabel('Image');

        $inputController->setStructure([
            ['chemist_name','chemist_code'],
            ['telephone','chemist_address'],
            ['credit_amount','chemist_class_id'],
            ['sub_twn_id','route_id'],
            ['chemist_type_id','chemist_mkd_id'],
            ['mobile_number','phone_no'],
            ['chemist_owner','credit_limit'],
            ['lat','lon'],
            ['email','updated_u_id'],
        ]); 
    }
}