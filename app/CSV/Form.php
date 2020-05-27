<?php
namespace App\CSV;

use App\Form\Form as BaseForm;
use App\Form\Inputs\Input;
use App\Exceptions\WebAPIException;
use Illuminate\Support\Facades\Hash;

class Form extends Base{
    /**
     * Form instance
     *
     * @var BaseForm
     */
    protected $form;
    /**
     * Eloquent model for the form
     *
     * @var \App\Models\Base
     */
    protected $model;
    /**
     * Parsing the form instance to class
     *
     * @param BaseForm $form
     * @param \App\Models\Base $model
     */
    public function __construct($form,$model){
        $this->form = $form;
        $this->model = $model;
    }
    /**
     * Returning the form name
     */
    public function getTitle(){
        return $this->form->getTitle();
    }

    public function getColumnNames()
    {
        $inputController = $this->form->getInputController();

        $inputs = $inputController->getOnlyPrivilegedInputs();

        return array_keys($inputs);
    }
    /**
     * Formating a column label
     *
     * @param string $name
     * @param Input $input
     * @return string
     */
    protected function formatColumnLabel($name,$input){
        $type = $input->getType();
        $label = $input->getLabel();

        switch ($type) {
            case 'ajax_dropdown':
                $label .= " Code";
                break;
            
            case 'select';
                $options = $input->getCustomProp('options');

                $label .= " { ".implode("|",array_values($options))." }";
                break;
        }

        return $label;
    }
    /**
     * Returning all column labels
     * 
     * @return string[]
     */
    public function getColumnLabels(){
        $inputController = $this->form->getInputController();

        $inputs = $inputController->getOnlyPrivilegedInputs();

        $inputLabels = [];

        foreach($inputs as $name=>$input){
            $inputLabels[$name] = $this->formatColumnLabel($name,$input);
        }

        return $inputLabels;
    }

    protected function formatValue($columnName, $value)
    {
        $input = $this->form->getInputController()->getInput($columnName);

        $type = $input->getType();

        switch ($type) {
            case 'ajax_dropdown':
                $value = $this->formatAjaxDropdownValue($input,$value);
                break;
            case 'select':
                $value = $this->formatSelectValue($input,$value);
                break;
            case 'password':
                $value = $this->formatPasswordValue($input,$value);
                break;
        }
        
        return $value;
    }

    protected function formatAjaxDropdownValue(Input $input,$value){
        $link = $input->getCustomProp('link');

        $className = ucfirst(camel_case($link));

        $className = "\App\Models\\".$className;
        /** @var \App\Models\Base */
        $model = new $className();
        /** @var string */
        $codeName = $model->getCodeName();

        $exists = $model::where($codeName,$value)->first();

        if(!$exists&&$value) throw new WebAPIException("Code mismatched for ".$input->getLabel().". Supplied value is $value ." );

        return $exists? $exists->getKey():null;
    }

    protected function formatSelectValue(Input $input,$value){
        $options = $input->getCustomProp('options');

        $supplied = $value;

        foreach ($options as $key => $label) {
            if(strtolower($label)==strtolower($value))
                $value = $key;
        }

        if(!$value){
            $message = "Invalid value for ".$input->getLabel().". supplied value is '$supplied' . Expecting one of ".implode(",",array_values($options));
            throw new WebAPIException($message);
        }

        return $value;
    }

    protected function formatPasswordValue(Input $input,$value){
        return Hash::make($value);
    }

    protected function insertRow($row)
    {
        $formatedRow = $this->form->beforeCreate($row);
        $inst = $this->model::create($formatedRow);
        $this->form->afterCreate($inst,$row);
    }
}