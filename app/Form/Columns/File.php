<?php

namespace App\Form\Columns;

use App\Form\Formaters\FileFormater;

class File extends Column{

    use FileFormater;

    protected $type='file';
}