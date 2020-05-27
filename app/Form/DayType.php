<?php
namespace App\Form;

class DayType extends Form{

    protected $title='Day Type';


    protected $dropdownDesplayPattern = 'dt_name';


    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('dt_name')->setLabel("Day Type");
        $inputController->text('dt_code')->setLabel("Code")->isUpperCase();
        $inputController->select('dt_color')->setLabel("Color")->setOptions(config('shl.color_codes'));
        $inputController->check('dt_is_working')->setLabel("Working Day")->setDefaultValue(0);
        $inputController->check('dt_field_work_day')->setLabel("Field Working Day")->setDefaultValue(0);
        $inputController->check('dt_bata_enabled')->setLabel("Applicable Bata Enabled");
        $inputController->check('dt_mileage_enabled')->setLabel("Mileage Enabled");
        $inputController->setStructure([
            ["dt_name"],
            ["dt_code","dt_color"],
            ["dt_bata_enabled", "dt_mileage_enabled","dt_is_working","dt_field_work_day"]
        ]);
    }

}