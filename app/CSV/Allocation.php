<?php

namespace App\CSV;

use App\Exceptions\WebAPIException;

class Allocation extends Base {
    /**
     * Mapping class for primary and secondary childs
     *
     * @var \App\Models\Base
     */
    protected $allocationsClass;
    /**
     * Main class for the mapping
     *
     * @var \App\Models\Base
     */
    protected $mainClass;
    /**
     * Children classes
     * 
     * mapping column name as key and class as value
     * 
     * @var \App\Models\Base[]
     */
    protected $childClasses = [];
    /**
     * Removing all old allocations before inserting new ones
     *
     * @return void
     */
    protected function beforeInsert(){
        // $primaryKey = $this->getMainKeyName();

        // $mainIds = array_column($this->data,$primaryKey);

        // if($this->allocationsClass != "App\Models\UserCustomer"){
        // $this->allocationsClass::whereIn($primaryKey,$mainIds)->delete();
        // }
    }

    protected function getMainKeyName(){
        return (new $this->mainClass)->getKeyName();
    }

    protected function getMainCodeName(){
        return (new $this->mainClass)->getCodeName();
    }

    protected function formatValue($columnName, $value)
    {
        $mainPrimary = $this->getMainKeyName();

        if($columnName==$mainPrimary){
            if(!isset($value)) throw new WebAPIException(class_basename($this->mainClass)." code not supplied.");

            $primaryCode = $this->getMainCodeName();

            $main = $this->mainClass::where($primaryCode,'like',trim($value))->first();

            if(!$main) throw new WebAPIException(class_basename($this->mainClass)." not found for the supplied code. Supplied code is '$value' .");

            return $main->getKey();
        }

        $exists = null;
        $foundValue = "";
        
        foreach ($this->childClasses as $key => $model) {
            /** @var \App\Models\Base */
            $childInst = new $model();
            $childCodeName = $childInst->getCodeName();

            if($columnName==$key){
                if(!empty(trim($value))){
                    $foundValue=$value;
                    $exists = $model::where($childCodeName,'like',trim($value))->first();

                    if(!$exists) throw new WebAPIException("Code mismatching error. supplied code is '$foundValue'. We can not find a record for this code");
                }
            }
        }

        return isset($exists)? $exists->getKey():null;
    }

    protected function insertRow($row)
    {
        $this->allocationsClass::firstOrCreate($row);
    }


}