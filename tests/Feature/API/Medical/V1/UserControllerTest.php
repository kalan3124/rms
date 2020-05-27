<?php

namespace Tests\Feature\API\Medical\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Models\User;

class UserControllerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLogin()
    {
        $user = User::find(1);

        $this->withoutMiddleware();
        $response = $this->actingAs($user,'api')->json('post','/api/medical/v1/login');
        
        $response->assertStatus(200);
    }
}
