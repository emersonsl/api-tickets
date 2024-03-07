<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Batch;
use App\Models\Event;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BatchControllerTest extends TestCase
{
    public function setUp(): void
    {
        Parent::setUp();
        
        $user = User::factory()->create();
        $user->assignRole('promoter');
        Sanctum::actingAs($user);
    }
    
    /**
     * Test create batch invalid fields
     */
    public function test_create_invalid_data(): void
    {
        $response = $this->post('/api/v1/batch/create', [
            'event_id' => 'invalid type'
        ]);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    public function test_create_event_not_found(): void
    {
        $maxId = Event::max('id');

        $batch = Batch::factory()->create();

        $data = [
            'event_id' => $maxId + 1,
            'sector_id' => $batch->sector_id,
            'title' => $batch->title,
            'quantity' => $batch->quantity,
            'value' => $batch->value,
            'release_date_time' => $batch->release_date_time->format('Y-m-d H:i:s'),
            'expiration_date_time' => $batch->expiration_date_time->format('Y-m-d H:i:s')
        ];

        $response = $this->post('/api/v1/batch/create', $data);

        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Event not found', $responseArray['message']);
    }

    public function test_create_sector_not_found(): void
    {
        $maxId = Sector::max('id');

        $batch = Batch::factory()->create();

        $data = [
            'event_id' => $batch->event_id,
            'sector_id' => $maxId + 1,
            'title' => $batch->title,
            'quantity' => $batch->quantity,
            'value' => $batch->value,
            'release_date_time' => $batch->release_date_time->format('Y-m-d H:i:s'),
            'expiration_date_time' => $batch->expiration_date_time->format('Y-m-d H:i:s')
        ];

        $response = $this->post('/api/v1/batch/create', $data);

        $response->assertStatus(404);

        $responseArray = $response->getData(true);
        
        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Sector not found', $responseArray['message']);
    }

    public function test_create_success(): void
    {
        $batch = Batch::factory()->create();

        $data = [
            'event_id' => $batch->event_id,
            'sector_id' => $batch->sector_id,
            'title' => $batch->title,
            'quantity' => $batch->quantity,
            'value' => $batch->value,
            'release_date_time' => $batch->release_date_time->format('Y-m-d H:i:s'),
            'expiration_date_time' => $batch->expiration_date_time->format('Y-m-d H:i:s')
        ];

        $response = $this->post('/api/v1/batch/create', $data);

        $response->assertStatus(200);

        $responseArray = $response->getData(true);
        
        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Batch created with sucesss', $responseArray['message']);
    }


}
