<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\AuthController;
use App\Models\User;
use App\Traits\HttpResponses;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use HttpResponses;
    
    /**
     * Test login invalide fields
     */
    public function test_login_invalid_data(): void
    {
        $response = $this->post('/api/v1/auth/login');
        
        $response->assertStatus(422);

        $responseArray = $response->getData(true);
        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test login success
     */
    public function test_login_success(): void
    {
        $response = $this->post('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ]);
        $response->assertStatus(200);

        $responseArray = $response->getData(true);
        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Authorized', $responseArray['message']); 
    }

    /**
     * Test login Unauthorized
     */
    public function test_login_Unauthorized(): void
    {
        $response = $this->post('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'admin1234',
        ]);

        $response->assertStatus(403);

        $responseArray = $response->getData(true);
        $this->assertEquals('403', $responseArray['status']); 
        $this->assertEquals('Unauthorized', $responseArray['message']); 
    }

    /**
     * Test logout success
     */
    public function test_logout_success(){
        $responseLogin = $this->post('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'admin123',
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/auth/logout');

        $responseArray = $response->getData(true);;

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Logout success', $responseArray['message']); 
    }
}
