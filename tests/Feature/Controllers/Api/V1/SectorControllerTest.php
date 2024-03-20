<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Event;
use App\Models\Sector;
use App\Models\Ticket;
use App\Models\User;
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
     * Test list sectors success
     */

     public function test_list_sector_successs(): void
     {
        $response = $this->get('/api/v1/sector');

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('List of Sectors', $responseArray['message']); 
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

    /**
     * Test update sector invalid fields
     */
    public function test_update_invalid_data(): void
    {
        $response = $this->put('/api/v1/sector/update', []);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test update sector not found
     */
    public function test_update_not_found(): void
    {
        $maxId = Sector::max('id');

        $response = $this->put('/api/v1/sector/update', [
            'id' => $maxId + 1,
            'title' => fake()->word()
        ]);

        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Sector not found', $responseArray['message']); 
    }

    /**
     * Test update sector success
     */
    public function test_update_success(): void
    {
        $id = Sector::first()->id;

        $response = $this->put('/api/v1/sector/update', [
            'id' => $id,
            'title' => fake()->word()
        ]);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Sector updated with success', $responseArray['message']); 
    }

    /**
     * Test sector success force delete
     */
    public function test_delete_success_force_delete(): void
    {
        $sector = Sector::factory()->create();

        $response = $this->delete("/api/v1/sector/delete/$sector->id");

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Sector deleted with success', $responseArray['message']); 
    }

    /**
     * Test sector success force delete
     */
    public function test_delete_success_soft_delete(): void
    {
        $result = Ticket::join('batches', 'tickets.batch_id', 'batches.id')->first();
        $id = $result->sector_id;

        $response = $this->delete("/api/v1/sector/delete/$id");

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Sector canceled with success, there are associated tickets', $responseArray['message']); 
    }
}