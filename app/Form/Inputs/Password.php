<?php
namespace App\Form\Inputs;

use App\Form\Formaters\PasswordFormater;


class Password extends Input{

    use PasswordFormater;

    protected $type = 'password';
    /**
     * Password hashing or not
     *
     * @var bool
     */
    protected $hash=true;

    public function isHashable($hash=false){
        $this->hash = $hash;
    }
}