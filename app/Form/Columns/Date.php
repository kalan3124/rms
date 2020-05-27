<?php

namespace App\Form\Columns;

use App\Form\Formaters\DateFormater;

class Date extends Column{

    use DateFormater;

    protected $type='date';
}