<?php
namespace App\Form\Columns;

use App\Form\Formaters\SelectFormater;

class Select extends Column{

    use SelectFormater;

    protected $type='select';

    protected $dropdownSearchable = false;

}