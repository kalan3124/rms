<?php

namespace App\Form\Formaters;

use App\Exceptions\WebAPIException;
use Illuminate\Support\Facades\Hash;

trait PasswordFormater {

    public function fetchValue($value,$other=[]){
        $password = $value;

        if(!$value)
            throw new WebAPIException("Password field is required");

        if(strlen($password)<8)
            throw new WebAPIException("Please type a strong password. Password should contain at least 8 characters.");
        
        if( !preg_match("#[0-9]+#", $password ) ) {
            throw new WebAPIException("Password must  at least one number!");
        }
        if( !preg_match("#[a-z]+#", $password ) ) {
            throw new WebAPIException("Password must  at least one letter!");
        }
        if( !preg_match("#[A-Z]+#", $password ) ) {
            throw new WebAPIException("Password must  at least one CAPS!");
        }
        if( !preg_match("#\W+#", $password ) ) {
            throw new WebAPIException("Password must  at least one symbol!");
        }

        return ($this->hash)?Hash::make($value):$value;
    }

}