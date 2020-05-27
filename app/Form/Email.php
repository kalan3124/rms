<?php
namespace App\Form;

use App\Models\EmailSelectedType;

class Email extends Form{

    protected $title='Mailing List';

    protected $dropdownDesplayPattern = 'e_name - e_address';

    public function setInputs(\App\Form\Inputs\InputController $inputController)
    {
        $inputController->text('e_email')->setLabel('Email')->isUpperCase(true);
        $inputController->text('e_name')->setLabel('Name');
        $inputController->ajax_dropdown('types')->setLabel("Types")->setMultiple(true)->setLink('email_type');

        $inputController->setStructure([
            ['e_email','e_name'],
            'types'
        ]);
    }

    public function formatResult($inst)
    {
        $formated = parent::formatResult($inst);

        $types = EmailSelectedType::with('emailType')->where('e_id',$inst->getKey())->get();

        $formated['types'] = $types->map(function(EmailSelectedType $emailSelectedType){
            if(!$emailSelectedType->emailType){
                return [
                    'value'=>0,
                    'label'=> 'DELETED'
                ];
            }

            return [
                'value'=> $emailSelectedType->emailType->et_id,
                'label'=> $emailSelectedType->emailType->et_name
            ];
        });

        return $formated;
    }

    public function afterCreate($inst, $values)
    {
        if(isset($values['types'])){
            foreach ($values['types'] as $key => $type) {
                EmailSelectedType::create([
                    'e_id'=> $inst->getKey(),
                    'et_id'=> $type['value']
                ]);
            }
        }
    }

    public function afterUpdate($inst, $values)
    {
        EmailSelectedType::where('e_id',$inst->getKey())->delete();

        $this->afterCreate($inst,$values);
    }

    public function afterDelete($id)
    {
        EmailSelectedType::where('e_id',$id)->delete();
    }
}
