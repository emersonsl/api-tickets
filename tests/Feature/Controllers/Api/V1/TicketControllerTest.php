<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Batch;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    public function setUp(): void
    {
        Parent::setUp();
        
        $user = User::factory()->create();
        $user->assignRole('customer');
        Sanctum::actingAs($user);
    }
    
    /**
     * Test create ticket invalid fields
     */
    public function test_create_invalid_data(): void
    {
        $response = $this->post('/api/v1/ticket/reserve', [ 
            'batch_id' => 'invalid type'
        ]);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test create ticket batch not found
     */
    public function test_create_batch_not_found(): void
    {
        $maxId = Batch::max('id');

        $data = [
            'batch_id' => $maxId + 1,
        ];

        $response = $this->post('/api/v1/ticket/reserve', $data);

        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Batch not found', $responseArray['message']);
    }

    /**
     * Test create ticket coupon check fail
     */
    public function test_create_coupon_check_fail(): void
    {
        $coupon = Coupon::all()->first();
        $batch = Batch::where('event_id', '<>', $coupon->event_id)->first();

        $data = [
            'batch_id' => $batch->id,
            'key_coupon' => $coupon->key
        ];

        $response = $this->post('/api/v1/ticket/reserve', $data);

        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Coupon not found', $responseArray['message']);
    }

    /**
     * Test create ticket batch sold out
     */
    public function test_create_batch_sold_out(): void
    {
        $coupon = Coupon::where('release_date_time', '<=', 'now()')
        ->where('expiration_date_time', '>=', 'now()')
        ->where('quantity', '>', 0)
        ->first();
        $batch = Batch::factory()->create([
            'event_id' => $coupon->event_id,
            'quantity' => 0,
            'value' => $coupon->value + 1
        ]);

        $data = [
            'batch_id' => $batch->id,
            'key_coupon' => $coupon->key
        ];

        $response = $this->post('/api/v1/ticket/reserve', $data);

        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Ticket cannot be reserved, sold out', $responseArray['message']);
    }

    /**
     * Test create ticket success
     */
    public function test_create_success(): void
    {
        $batch = Batch::all()->first();

        $data = [
            'batch_id' => $batch->id,
        ];

        $response = $this->post('/api/v1/ticket/reserve', $data);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Ticket reserved with success', $responseArray['message']);
    }

    /**
     * Test create ticket success with ticket
     */
    public function test_create_success_with_ticket(): void
    {
        $coupon = Coupon::where('release_date_time', '<=', 'now()')
        ->where('expiration_date_time', '>=', 'now()')
        ->where('quantity', '>', 0)
        ->first();
        $batch = Batch::factory()->create([
            'event_id' => $coupon->event_id,
            'value' => $coupon->value + 1
        ]);

        $data = [
            'batch_id' => $batch->id,
            'key_coupon' => $coupon->key
        ];

        $response = $this->post('/api/v1/ticket/reserve', $data);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Ticket reserved with success', $responseArray['message']);
    }
}
