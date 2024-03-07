<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{

    public function setUp(): void
    {
        Parent::setUp();
        
        $user = User::factory()->create();
        $user->assignRole('customer');
        Sanctum::actingAs($user);
    }
    
    /**
     * Test create payment invalid fields
     */
    public function test_create_invalid_data_struct(): void
    {
        $response = $this->post('/api/v1/payment/create', [ 
            'ticked_id' => 'invalid type'
        ]);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test create payment ticket not found
     */
    public function test_create_ticket_not_found(): void
    {
        $maxId = Ticket::max('id');

        $response = $this->post('/api/v1/payment/create', [ 
            'ticket_id' => $maxId + 1 
        ]);
        
        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Ticket not found', $responseArray['message']);
    }

    /**
     * Test create payment access unathorized
     */
    public function test_create_access_unathorized(): void
    {
        $user = Auth::user();
        $id = Ticket::where('user_id', '<>', $user->id)->first()->id;

        $response = $this->post('/api/v1/payment/create', [ 
            'ticket_id' => $id + 1 
        ]);
        
        $response->assertStatus(403);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('403', $responseArray['status']); 
        $this->assertEquals('Access unathorized', $responseArray['message']);
    }

    /**
     * Test create payment access unathorized
     */
    public function test_create_success(): void
    {
        $user = Auth::user();
        $ticket = Ticket::all()->first();
        $ticket->user_id = $user->id;
        $ticket->save();


        $response = $this->post('/api/v1/payment/create', [ 
            'ticket_id' => $ticket->id 
        ]);
        
        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals(200, $responseArray['status']); 
        $this->assertEquals('Payment created with success', $responseArray['message']);
    }

}
