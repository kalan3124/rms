<?php
namespace App\Form\Columns;

use App\Form\Formaters\OtherInputsFormater;
use App\Form\Inputs\Input;

/**
 * Column Base Model
 * 
 * @method static setLabel(string $label)
 * @method static setName(string $name)
 * @method static setType(string $type)
 * @method static setInput(Input $input)
 */
class Column implements \JsonSerializable{

    use OtherInputsFormater;
    
    protected $name;

    protected $label;

    protected $type;

    protected $searchable = true;

    protected $input;

    protected $dropdownSearchable = true;

    protected $customProps = [];

    public function __call(string $name, array $params){

        $propertyName = strtolower(substr($name,3));

        if(!in_array($propertyName,['name','label','type','input'])) throw new \BadMethodCallException("Invalid method called");

        if(substr($name,0,3)=='set'){
            $this->{$propertyName} = $params[0];
            return $this;
        } else {
            return $this->{$propertyName};
        }
    }
    /**
     * Returning the search condition
     * 
     * @param mixed $value
     * @return array [columnName,comparison,value]
     */
    public function getSearchCondition($value){
        return [$this->name,'=',$value];
    }
    /**
     * Checking the weather the column is searchable column
     * 
     * @return boolean
     */
    public function isSearchable(){
        return $this->searchable;
    }

    public function isDropdownSearchable(){
        return $this->dropdownSearchable;
    }

    public function setSearchable($search=false){
        $this->searchable = $search;
        return $this;
    }

    public function setType($type){
        $this->type = $type;
        return $this;
    }

    public function setCustomProp($name,$value){
        $this->customProps[$name] = $value;
    }

    public function toArray(){
        return array_merge([
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'searchable' => $this->isSearchable(),
        ],$this->customProps);
    }

    public function jsonSerialize(){
        return $this->toArray();
    }

}