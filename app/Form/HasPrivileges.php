<?php

namespace App\Form;

use Illuminate\Support\Facades\Auth;
/**
 * This class is checkign the user privileges for the given instance
 * 
 * @method self setRestrictedUserRolls()
 * @method self setPrivilegedUserRolls()
 * @method self setRestrictedUsers()
 * @method self setPrivilegedUsers()
 */
class HasPrivileges{
    protected $restrictedUserRolls = [];

    protected $privilegedUserRolls = [];

    protected $restrictedUsers = [];

    protected $privilegedUsers = [];
    /**
     * Logged user. Default is Auth::user
     *
     * @var \App\Models\User
     */
    protected $loggedUser;
    /**
     * Setting the logged user
     * 
     * @param \App\Models\User $user ['Auth::user']
     */
    public function setLoggedUser($user){
        $this->loggedUser = $user;
    }
    /**
     * Returning the logged user
     *
     * @return \App\Models\User
     */
    public function getLoggedUser(){
        $loggedUser = $this->loggedUser;

        if(!$loggedUser) return Auth::user();

        return $loggedUser;
    }

    /**
     * Checking the weather current user is privileged for the instance
     *
     * @return boolean
     */
    public function isPrivileged(){
        $user = $this->getLoggedUser();
        
        if(!$user) return true;

        $userRoll = $user->getRoll();

        if(count($this->restrictedUserRolls)){
            // If user restricted user rolls are set and current user's roll is in this category
            if(in_array($userRoll,$this->restrictedUserRolls)) return false;
        }

        if(count($this->privilegedUserRolls)){
            // If user privileged user rolls are set and current user's roll is not in this category
            if(!in_array($userRoll,$this->privilegedUserRolls)) return false;
        }

        if(count($this->restrictedUsers)){
            // If user restricted user ids are set and current user is in this category
            if(in_array($user->getKey(),$this->restrictedUsers)) return false;
        }

        if(count($this->privilegedUsers)){
            // If user privileged user ids are set and current user is not in this category
            if(!in_array($user->getKey(),$this->privilegedUsers)) return false;
        }

        return true;
    }
    /**
     * Setting Privileges
     *
     * @param string $name
     * @param array $param
     * @return self
     */
    public function __call(string $name,array $param){
        if(substr($name,0,3)=='set'){
            $property = lcfirst(substr($name,3));
            $this->{$property} = $param[0];
            return $this;
        }
    }
}