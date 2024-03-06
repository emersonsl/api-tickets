<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SectorControllerTest extends TestCase
{
    public function setUp(): void
    {
        Parent::setUp();
        
        $user = User::factory()->create();
        $user->assignRole('promoter');
        Sanctum::actingAs($user);
    }
    
    /**
     * Test create sector invalid fields
     */
    public function test_create_invalid_data(): void
    {
        $response = $this->post('/api/v1/sector/create', [
            'event_id' => 'invalid type'
        ]);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test create sector event not found
     */
    public function test_create_event_not_found(): void
    {
        $maxId = Event::max('id');

        $data = [
            'event_id' => $maxId + 1,
            'title' => fake()->word()
        ];

        $response = $this->post('/api/v1/sector/create', $data);

        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Event not found', $responseArray['message']);
    }

    /**
     * Test create sector success
     */
    public function test_create_success(): void
    {
        $id = Event::all()->first()->id;

        $data = [
            'event_id' => $id,
            'title' => fake()->word()
        ];

        $response = $this->post('/api/v1/sector/create', $data);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Sector created with success', $responseArray['message']);
    }
}