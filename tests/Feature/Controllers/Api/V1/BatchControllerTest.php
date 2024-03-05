<?php

namespace Tests\Feature\Controllers\Api\V1;

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
        
    }


}
