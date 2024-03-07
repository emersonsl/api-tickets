<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    /**
     * Test create user invalid fields
     */
    public function test_create_invalid_data(): void
    {
        $response = $this->post('/api/v1/users/register', [ 
            'phone_number' => 'invalid type'
        ]);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test create user email already exists
     */
    public function test_create_email_already_exists(): void
    {
        $oldUser = User::all()->first();
        $newUser = User::factory()->make(['email' => $oldUser->email]);

        $response = $this->post('/api/v1/users/register',
            $newUser->getAttributes()            
        );

        $response->assertStatus(409);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('409', $responseArray['status']); 
        $this->assertEquals('Email already exists in the database', $responseArray['message']); 
    }

    /**
     * Test create user success
     */
    public function test_create_success(): void
    {
        $newUser = User::factory()->make();

        $response = $this->post('/api/v1/users/register',
            $newUser->getAttributes()            
        );

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Success in register user', $responseArray['message']); 
    }

    /**
     * Autheticate admin user
     */
    private function authAdmin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user);
    }

    /**
     * Test promote user invalid fields
     */
    public function test_promote_invalid_data(): void
    {
        $this->authAdmin();

        $response = $this->put('/api/v1/users/promote', []);
        
        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test promote user, user not found
     */
    public function test_promote_user_not_found(): void
    {
        $this->authAdmin();

        $email = fake()->unique()->safeEmail();
        $role = Role::all()->first()->name;

        $response = $this->put('/api/v1/users/promote', [
            'email' => $email,
            'role' => $role
        ]);
        
        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Itens not found', $responseArray['message']); 
    }

    /**
     * Test promote user role not found
     */
    public function test_promote_role_not_found(): void
    {
        $this->authAdmin();

        $email = User::all()->first()->email;
        $role = fake()->word();

        $response = $this->put('/api/v1/users/promote', [
            'email' => $email,
            'role' => $role
        ]);
        
        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Itens not found', $responseArray['message']); 
    }

    /**
     * Test promote user success
     */
    public function test_promote_success(): void
    {
        $this->authAdmin();

        $email = User::all()->first()->email;
        $role = Role::all()->first()->name;

        $response = $this->put('/api/v1/users/promote', [
            'email' => $email,
            'role' => $role
        ]);
        
        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Success in promote user', $responseArray['message']); 
    }
}
