<?php

namespace App\Form\Actions;

use App\Form\HasPrivileges;

class Action extends HasPrivileges{
    /**
     * Action name
     *
     * @var string
     */
    protected $name;
    
    public function getName(){
        return $this->name;
    }
}