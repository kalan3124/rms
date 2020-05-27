<?php
namespace App\Models;
use BadMethodCallException;
use JsonSerializable;

class SettersAndGetters  implements JsonSerializable  {
    protected $properties = [];
    /**
     * Setters and getters
     */
    public function __call($name, $arguments)
    {
        $propertyName = lcfirst( substr($name,3));
        $method = substr($name,0,3);
        if(!in_array($propertyName,$this->properties)){
            throw new BadMethodCallException("Called to an invalid method $name");
        }
        if($method=="set"){
            if(isset($arguments[0])){
                $this->{$propertyName} = $arguments[0];
            }
        } else if ($method=="get") {
            return $this->{$propertyName};
        } else {
            throw new BadMethodCallException("Called to an invalid method $name");
        }
    }



    public function jsonSerialize()
    {
        $arr = [];

        foreach($this->properties as $property){
            $arr[$property] = $this->{$property};
        }

        return $arr;
    }
}