<?php
namespace App\Form\Inputs;


use App\Form\Inputs\AjaxDropdown;
use App\Form\Inputs\Check;
use App\Form\Inputs\Date;
use App\Form\Inputs\File;
use App\Form\Inputs\Select;
use App\Form\Inputs\Text;
use App\Form\Inputs\TreeSelect;

/**
 * Managing inputs in the data table
 * 
 * @method AjaxDropdown ajax_dropdown(string $columnName)
 * @method Check check(string $columnName)
 * @method Date date(string $columnName)
 * @method File file(string $columnName)
 * @method Select select(string $columnName)
 * @method Text text(string $columnName)
 * @method TreeSelect tree_select(string $columnName)
 */
class InputController{
    /**
     * Storing inputs
     *
     * @var Input[]
     */
    protected $inputs = [];

    protected $structure = [];

    public function __call(string $string,array $params){

        $className = 'App\Form\Inputs\\'.ucfirst(camel_case($string));

        if(class_exists($className)){
            $instance = new $className();
        } else {
            $instance = new \App\Form\Inputs\Other();
            $instance->setType($string);
        }


        if(count($params)>0&&is_string($params[0])){
            $instance->setName($params[0]);
        } else {
            throw new \ArgumentCountError("Too few arguments supplied to $string method. expected a one argument as input name.");
        }

        $this->inputs[$params[0]] = $instance;

        return $instance;
    }
    /**
     * Returning an input by id 
     *
     * @param string $id
     * @return Input
     */
    public function getInput($id){
        return $this->inputs[$id];
    }
    /**
     * Returning only privileged inputs for user
     *
     * @return Input[]
     */
    public function getOnlyPrivilegedInputs(){
        $privilegedInputs = [];

        foreach($this->inputs as $name=>$input){
            if($input->isPrivileged()){
                $privilegedInputs[$name] = $input;
            }
        }

        return $privilegedInputs;
    }
    /**
     * Setting form grid structure
     *
     * @param array $arr ['input_name',['input_name2','input_name3'],'input_name4']
     * @return void
     */
    public function setStructure(array $arr){
        $this->structure = $arr;
    }
    /**
     * Returning the structure
     *
     * @param array $inputNames
     * @return array
     */
    public function getStructure($inputNames=null){
        $filteredStructure = [];

        $privilegedInputNames = array_keys($this->getOnlyPrivilegedInputs());

        if(empty($this->structure)&&empty($inputNames)) $inputNames = array_keys($this->inputs);
        elseif(!isset($inputNames)) $inputNames = $this->structure;

        foreach($inputNames as $group){
            if(is_string($group)){
                if(in_array($group,$privilegedInputNames)) $filteredStructure[]= $group;
            } else{
                $filteredGroup = $this->getStructure($group);

                $filteredStructure[]= $filteredGroup;
            }
        }

        return $filteredStructure;
    }
    /**
     * Returning all default values
     * 
     * @return array
     */
    public function getDefaultValues(){
        $defaultValues = [];

        foreach($this->inputs as $name=>$input){
            $defaultValues[$name] = $input->getDefaultValue();
        }

        return $defaultValues;
    }
}