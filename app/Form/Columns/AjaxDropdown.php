<?php
namespace App\Form\Columns;

use App\Form\Formaters\AjaxDropdownFormater;

class AjaxDropdown extends Column{

    use AjaxDropdownFormater;

    protected $type='ajax_dropdown';

    protected $dropdownSearchable = false;

}