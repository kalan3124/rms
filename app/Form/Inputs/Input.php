<?php
namespace App\Form\Inputs;

use App\Form\HasPrivileges;
use App\Form\Formaters\OtherInputsFormater;

class Input extends HasPrivileges implements \JsonSerializable{

    use OtherInputsFormater;

    protected $name;

    protected $type;

    protected $label;

    protected $defaultValue=null;

    protected $customProps = [];

    protected $validations = 'required';
    /**
     * Setting custom props to input field
     * 
     * @param string $name property name
     * @param mixed $value
     */
    protected function setCustomProp(string $name,$value){
        $this->customProps[$name] = $value;
    }
    /**
     * Setting the input type name
     * 
     * @param string $typeName
     * @return static
     */
    public function setType(string $typeName){
        $this->type = $typeName;
        return $this;
    }
    /**
     * Returning type name for the input
     * 
     * @return string
     */
    public function getType(){
        return $this->type;
    }
    /**
     * Setting the name for the input
     * 
     * @param string $name
     * @return static
     */
    public function setName($name){
        $this->name=$name;
        return $this;
    }
    /**
     * Returning the name for the input
     * 
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * Setting the label for the input
     * 
     * This label is despalying as the html label above your form input
     * 
     * @param string $label
     * @return static
     */
    public function setLabel(string $label){
        $this->label = $label;
        return $this;
    }
    /**
     * Returning the label for the input
     * 
     * @return string
     */
    public function getLabel(){
        return $this->label;
    }
    /**
     * Setting a default value for the input
     * 
     * @param mixed $default
     * @return static
     */
    public function setDefaultValue($default){
        $this->defaultValue = $default;
        return $this;
    }
    /**
     * Returning the default value
     * 
     * If you provided a callback function to the setDefaultValue() method this function will execute it and return the value
     * 
     * @return mixed
     */
    public function getDefaultValue(){
        if(is_callable($this->defaultValue)){
            return $this->defaultValue();
        } else {
            return $this->defaultValue;
        }
    }
    /**
     * Returning the custom props for a input
     * 
     * @return array
     */
    public function getCustomProps(){
        return $this->customProps;
    }
    /**
     * Returning a custom property by name
     * 
     * @param string $propName
     */
    public function getCustomProp($propName){
        return $this->customProps[$propName];
    }

    public function toArray(){
        $defaultProps = [
            'type' => $this->getType(),
            'label' => $this->getLabel(),
            'validations'=>$this->getValidations()
        ];

        $customProps = $this->getCustomProps();

        return array_merge($defaultProps, $customProps);
    }

    public function jsonSerialize(){
        return $this->toArray();
    }

    public function setValidations($validations = 'required'){
        $this->validations = $validations;
    }

    public function getValidations(){
        return $this->validations;
    }
}