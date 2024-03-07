<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Constants;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    /**
     * Test create webhook invalid fields
     */
    public function test_create_invalid_data(): void
    {
        $response = $this->post('/api/v1/webhook', []);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test create webhook payment not found
     */
    public function test_create_payment_not_found(): void
    {
        $uuid = fake()->uuid();

        $response = $this->post('/api/v1/webhook', [
            'external_id' => $uuid
        ]);

        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Payment not found', $responseArray['message']); 
    }

    /**
     * Test create webhook inconsistent hash
     */
    public function test_create_inconsistent_hash(): void
    {
        $invalidHash = fake()->uuid();
        $payment = Payment::all()->first();

        $response = $this->post('/api/v1/webhook', [
            'external_id' => $payment->id,
            'hash' => $invalidHash
        ]);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Inconsistent hash', $responseArray['message']); 
    }

    /**
     * Test create webhook success paid
     */
    public function test_create_success_paid(): void
    {
        $payment = Payment::all()->first();

        $response = $this->post('/api/v1/webhook', [
            'external_id' => $payment->id,
            'hash' => $payment->hash,
            'amount' => $payment->amount
        ]);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Webhook created with success', $responseArray['message']); 
        $this->assertEquals(Constants::PAYMENT_STATUS_PAID, $responseArray['data']['payment']['status']); 
    }

    /**
     * Test create webhook success paid lower
     */
    public function test_create_success_paid_lower(): void
    {
        $payment = Payment::all()->first();

        $response = $this->post('/api/v1/webhook', [
            'external_id' => $payment->id,
            'hash' => $payment->hash,
            'amount' => $payment->amount - 1
        ]);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Webhook created with success', $responseArray['message']); 
        $this->assertEquals(Constants::PAYMENT_STATUS_PAID_LOWER, $responseArray['data']['payment']['status']); 
    }

    /**
     * Test create webhook success paid over
     */
    public function test_create_success_paid_over(): void
    {
        $payment = Payment::all()->first();

        $response = $this->post('/api/v1/webhook', [
            'external_id' => $payment->id,
            'hash' => $payment->hash,
            'amount' => $payment->amount + 1
        ]);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Webhook created with success', $responseArray['message']);
        $this->assertEquals(Constants::PAYMENT_STATUS_PAID_OVER, $responseArray['data']['payment']['status']);
    }
}
