<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Address;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    public function setUp(): void
    {
        Parent::setUp();
        
        $user = User::factory()->create();
        $user->assignRole('promoter');
        Sanctum::actingAs($user);
    }
    
    /**
     * Test create event invalid fields
     */
    public function test_create_invalid_data_struct(): void
    {
        $response = $this->post('/api/v1/event/create', [ 
            'address' => [
                'number' => 'invalid type'
            ]
        ]);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test create event invalid fields
     */
    public function test_create_invalid_data(): void
    {
        $response = $this->post('/api/v1/event/create', [ 
            'address' => [
                'number' => 'invalid type'
            ],
            'event' => [
                'date_time' => 'invalid type'
            ]
        ]);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test create event success
     */
    public function test_create_success(): void
    {
        $event = Event::factory()->create();
        $address = Address::factory()->create();

        $response = $this->post('/api/v1/event/create', [ 
            'address' => [
                'street' => $address->street,
                'district' => $address->district,
                'number' => $address->number,
                'city' => $address->city,
                'state' => $address->state,
                'country' => $address->country,
                'post_code' => $address->post_code,
                'complement' => $address->complement
            ],
            'event' => [
                'title' => $event->title,
                'date_time' => $event->date_time
            ]
        ]);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Event created with success', $responseArray['message']); 
    }

    /**
     * Test list events upcoming success
     */
    public function test_list_upcoming_success(): void
    {
        $response = $this->get('/api/v1/event/upcoming');

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('List of Events Upcoming', $responseArray['message']); 
    }

    /**
     * Test list events available success
     */
    public function test_list_available_success(): void
    {
        $response = $this->get('/api/v1/event/available');

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('List of Events Available', $responseArray['message']); 
    }
}
