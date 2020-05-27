<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class UserTest extends TestCase
{
    protected $user;

    protected function makeUser(){
        $this->user = factory(User::class)->make();
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetRoll()
    {
        $this->makeUser();
        $this->assertInternalType("int",$this->user->getRoll());
    }
    /**
     * Checking the name is string
     */
    public function testGetName()
    {
        $this->makeUser();
        $this->assertInternalType("string",$this->user->getName());
    }

    public function testGetRollName(){
        $this->makeUser();
        $this->assertInternalType("string",$this->user->getRollName());
    }

    public function testGetProfilePicture(){
        $this->makeUser();
        $profilePicture = $this->user->getProfilePicture();
        $this->assertTrue(is_string($profilePicture)||is_null($profilePicture));
    }

    public function testGetPhoneNumber(){
        $this->makeUser();
        $phone = $this->user->getPhoneNumber();
        $this->assertTrue(is_string($phone)||is_null($phone));
    }

    public function testGetEmail(){
        $this->makeUser();
        $email = $this->user->getEmail();
        $this->assertTrue(is_string($email)||is_null($email));
    }
}
