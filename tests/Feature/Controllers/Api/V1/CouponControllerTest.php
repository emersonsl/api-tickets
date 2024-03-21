<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\CouponController;
use App\Models\Coupon;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CouponControllerTest extends TestCase
{
    public function setUp(): void
    {
        Parent::setUp();
        
        $user = User::factory()->create();
        $user->assignRole('promoter');
        Sanctum::actingAs($user);
    }

    /**
     * Test list coupon success
     */

     public function test_list_successs(): void
     {
        $response = $this->get('/api/v1/coupon');

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('List of Coupons', $responseArray['message']); 
     }
    
    /**
     * Test create batch invalid fields
     */
    public function test_create_invalid_data(): void
    {
        $response = $this->post('/api/v1/coupon/create', [
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

        $coupon = Coupon::factory()->create();

        $data = [
            'event_id' => $maxId + 1,
            'key' => $coupon->key,
            'quantity' => $coupon->quantity,
            'value' => $coupon->value,
            'release_date_time' => $coupon->release_date_time->format('Y-m-d H:i:s'),
            'expiration_date_time' => $coupon->expiration_date_time->format('Y-m-d H:i:s')
        ];

        $response = $this->post('/api/v1/coupon/create', $data);

        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Event not found', $responseArray['message']);
    }

    public function test_create_success(): void
    {
        $coupon = Coupon::factory()->create();

        $data = [
            'event_id' => $coupon->event_id,
            'key' => fake()->word(),
            'quantity' => $coupon->quantity,
            'value' => $coupon->value,
            'release_date_time' => $coupon->release_date_time->format('Y-m-d H:i:s'),
            'expiration_date_time' => $coupon->expiration_date_time->format('Y-m-d H:i:s')
        ];

        $response = $this->post('/api/v1/coupon/create', $data);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Coupon created with success', $responseArray['message']);
    }

    public function test_check_coupon_not_found(): void
    {
        $coupon = Coupon::factory()->create();
        $maxId = Event::max('id');

        $response = CouponController::check($coupon->key, $maxId + 1);

        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Coupon not found', $response['error']);
    }

    public function test_check_coupon_exceeds_the_maximum_allowable_value(): void
    {
        $coupon = Coupon::factory()->create();
        $value_batch = $coupon->value - 1;

        $response = CouponController::check($coupon->key, $coupon->event_id, $value_batch);

        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Coupon cannot be applied, exceeds the maximum allowable value', $response['error']);
    }

    public function test_check_coupon_not_realesed(): void
    {
        $coupon = Coupon::factory()->create();
        $coupon->release_date_time = fake()->dateTimeBetween('+ 1 second', '+ 30 day')->format('Y-m-d H:i:s');
        
        $coupon->save();

        $response = CouponController::check($coupon->key, $coupon->event_id);

        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Coupon cannot be applied, not realesed', $response['error']);
    }

    public function test_check_coupon_expired(): void
    {
        $coupon = Coupon::factory()->create();
        $coupon->release_date_time = fake()->dateTimeBetween('- 30 day', '-2 minute')->format('Y-m-d H:i:s');
        $coupon->expiration_date_time = fake()->dateTimeBetween('-30 day', '-1 second')->format('Y-m-d H:i:s');

        $coupon->save();

        $response = CouponController::check($coupon->key, $coupon->event_id);

        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Coupon cannot be applied, expired', $response['error']);
    }

    public function test_check_coupon_sold_out(): void
    {
        $coupon = Coupon::factory()->create();
        $coupon->release_date_time = fake()->dateTimeBetween('- 30 day', '-2 minute')->format('Y-m-d H:i:s');
        $coupon->quantity = 0;

        $coupon->save();

        $response = CouponController::check($coupon->key, $coupon->event_id);

        $this->assertEquals(false, $response['success']);
        $this->assertEquals('Coupon cannot be applied, sold out', $response['error']);
    }

    public function test_check_coupon_success(): void
    {
        $coupon = Coupon::factory()->create();
        $coupon->release_date_time = fake()->dateTimeBetween('- 30 day', '-2 minute')->format('Y-m-d H:i:s');

        $coupon->save();

        $response = CouponController::check($coupon->key, $coupon->event_id);

        $this->assertEquals(true, $response['success']);
        $this->assertEquals($coupon->id, $response['coupon']->id);
    }

    /**
     * Test coupon success force delete
     */
    public function test_delete_success_force_delete(): void
    {
        $coupon = Coupon::factory()->create();

        $response = $this->delete("/api/v1/coupon/delete/$coupon->id");

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Coupon deleted with success', $responseArray['message']); 
    }

    /**
     * Test event event success force delete
     */
    public function test_delete_success_soft_delete(): void
    {
        $result = Ticket::join('batches', 'tickets.batch_id', 'batches.id')->whereNotNull('tickets.coupon_id')->get()->first();
        $id = $result->coupon_id;

        $response = $this->delete("/api/v1/coupon/delete/$id");

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Coupon canceled with success, there are associated tickets', $responseArray['message']); 
    }   
}
