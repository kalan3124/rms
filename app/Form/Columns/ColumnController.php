<?php
namespace App\Form\Columns;

use App\Form\Columns\AjaxDropdown;
use App\Form\Columns\Button;
use App\Form\Columns\Check;
use App\Form\Columns\Custom;
use App\Form\Columns\Date;
use App\Form\Columns\File;
use App\Form\Columns\Link;
use App\Form\Columns\MultipleAjaxDropdown;
use App\Form\Columns\Number;
use App\Form\Columns\Select;
use App\Form\Columns\Text;
use App\Form\Columns\TreeSelect;

/**
 * Managing columns in the data table
 * 
 * @method AjaxDropdown ajax_dropdown(string $columnName)
 * @method Button button(string $columnName)
 * @method Check check(string $columnName)
 * @method Custom custom(string $columnName)
 * @method Date date(string $columnName)
 * @method File file(string $columnName)
 * @method Link link(string $columnName)
 * @method MultipleAjaxDropdown multiple_ajax_dropdown(string $columnName)
 * @method Number number(string $columnName)
 * @method Select select(string $columnName)
 * @method Text text(string $columnName)
 * @method TreeSelect tree_select(string $columnName)
 */
class ColumnController {

    protected $columns = [];

    public function __call(string $name,array $params){
        $className = 'App\Form\Columns\\'.ucfirst(camel_case($name));

        if(class_exists($className)){
            $instance = new $className();
        } else {
            $instance = new \App\Form\Columns\Other();
            $instance->setType($name);
        }


        if(count($params)>0&&is_string($params[0])){
            $instance->setName($params[0]);
        } else {
            throw new \ArgumentCountError("Too few arguments supplied to $name method. expected a one argument as input name.");
        }

        $this->columns[$params[0]] = $instance;

        return $instance;
    }

    public function getColumns(){
        return $this->columns;
    }

    public function getColumn($name){
        return $this->columns[$name];
    }
}