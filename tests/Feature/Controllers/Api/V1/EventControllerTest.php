<?php

namespace Tests\Feature\Controllers\Api\V1;

use App\Models\Address;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
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

    /**
     * Test upload banner event invalid fields
     */
    public function test_upload_banner_invalid_data(): void
    {
        $response = $this->post('/api/v1/event/uploadbanner', [ 
            'event_id' => 'invalid type'
        ]);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    private function createImageFile(): UploadedFile{
        $filename = fake()->word . '.png';
        $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAHUAAAAcCAMAAABoIQAcAAAASFBMVEUAAABlK4VlK4VlKINjKoVlK4VkKoNkKYNlK4VgKoBlKIVkKoNkKoRlK4VwIIBlK4VjKYNkK4RjLYNmK4ZlKodlKoVmKoNlK4W/Nw0oAAAAF3RSTlMA378fYO+AQJ8QMJBwzxCvUM9Qr3+wgHDNfUAAAALnSURBVEjHrVXbtusgCAwGL1Fza7tP/v9PT4JQtK6ulYfOS1sKMwoCQ05xmLwLIbhtGt6IeJlS9oPi9EPntjgZiLVndqfby77op3duHATZ4TsW08mH/N9xHMEejCAB/hBAlrgx2bfViuyLbdcHeZ5f/iTCncapxJpW4mgALIuVLfCdbO2I5QJzbVuZz4jqev4YSRQ+JI4WRu5qwZxgBQ2E8jEXt1DuyZzxUzWJKpDbbixRiGryy7B44uBC8p0XRywsAH45TQjkJnVwy5WIJ/s1qsiqSLGUsCsWi+q/6mxpaOH245Djjlp0I/5c9slSDVWVjRRjNHakupPqJF6cuxbRlL+UzVKaClusTuxFVZCLmq1oV4ploZaux1izQaUqNteoqnWsbFIptSid9Fd2F3ybBj2cvFFlU1UFTuXE4BjzF1XuLwGgKInC9q6rvokJ6rqqpORJ8UWVa67IogB08mylm+gNv+Kp6WfORcNnYOQTNZi/qF5uNmU8Aec3Du2nybNhw05VWiI+XFBEeU2talRefTHNfImc16A2zrXyacf0OPgudcjjav4qUsrExbZu0bctusb3bWC41u0SkWT6apb80WevGknjgYhRd9BFuZBtqfmidpuVFLZ3dppNT+OKM2wkklUh9LlKgL3tXYA4E41oyNXiNskEn014Aj8THm+Qvcey3OSVuM2fiFM9ZwL6C+8jRV4IvCZQW8KaZzBg2Ra6LhmyGkS13mlybycG4sShM8pUb/nww89myXun+jgabNpNnQRUFgaKjdfafDV+2Gn/OS3dmAyleQ1ylNGZKtQy27rbio5jcTUA+5oXLfdSPC2sjubmTFL3sUS0rKAYaTiF2yTnfbb7kpp//LKU7uGm88Oh5iQX1ZE2EmP6raq24avekZsOTp0wv1bN1DFu84iZBuNI1ysNu2FpbPytar+tEiegXUQ/VZWVo3gusnKaRXQbCfab3hgMHNRyyYvNJ0MNC3vYlvui/wFklmFjtR8ItgAAAABJRU5ErkJggg==');
        
        $tempFilePath = sys_get_temp_dir() . '/' . $filename;
        file_put_contents($tempFilePath, $imageData);
        
        $uploadedFile = new UploadedFile(
            $tempFilePath, 
            $filename,     
            'image/png',  
            null,          
            true           
        );
        
        return $uploadedFile;
    }

    /**
     * Test upload banner event not found
     */
    public function test_upload_banner_event_not_found(): void
    {
        $maxId = Event::max('id');
        
        $data = [
            'event_id' => $maxId + 1,
            'banner' => $this->createImageFile()
        ];

        $response = $this->post('/api/v1/event/uploadbanner', $data);
        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Event not found', $responseArray['message']);
    }

    /**
     * Test upload banner success
     */
    public function test_upload_banner_success(): void
    {
        $id = Event::all()->first()->id;

        $data = [
            'event_id' => $id,
            'banner' => $this->createImageFile()
        ];

        $response = $this->post('/api/v1/event/uploadbanner', $data);
        
        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Banner upload with success', $responseArray['message']);
    }

    /**
     * Test update event invalid fields
     */
    public function test_update_invalid_data(): void
    {
        $response = $this->put('/api/v1/event/update', []);

        $response->assertStatus(422);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('422', $responseArray['status']); 
        $this->assertEquals('Invalid data', $responseArray['message']); 
    }

    /**
     * Test update event invalid fields
     */
    public function test_update_invalid_data_1(): void
    {
        $response = $this->put('/api/v1/event/update', [ 
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
     * Test update event not found
     */
    public function test_update_event_not_found(): void
    {
        $maxId = Event::max('id');
        
        $response = $this->put('/api/v1/event/update', [ 
            'address' => [
                'number' => '50'
            ],'event' => [
                'id' => $maxId + 1
            ]
        ]);

        $response->assertStatus(404);
        
        $responseArray = $response->getData(true);
        
        $this->assertEquals('404', $responseArray['status']); 
        $this->assertEquals('Event not found', $responseArray['message']);
    }

    /**
     * Test update event success
     */
    public function test_update_success(): void
    {
        $event = Event::factory()->create();

        $response = $this->put('/api/v1/event/update', [ 
            'address' => [
                'number' => '50'
            ],'event' => [
                'id' => $event->id
            ]
        ]);

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Event updated with success', $responseArray['message']); 
    }

    /**
     * Test event event success force delete
     */
    public function test_delete_success_force_delete(): void
    {
        $event = Event::factory()->create();

        $response = $this->delete("/api/v1/event/delete/$event->id");

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Event deleted with success', $responseArray['message']); 
    }

    /**
     * Test event event success force delete
     */
    public function test_delete_success_soft_delete(): void
    {
        $result = Ticket::join('batches', 'tickets.batch_id', 'batches.id')->first();
        $id = $result->event_id;

        $response = $this->delete("/api/v1/event/delete/$id");

        $response->assertStatus(200);
        
        $responseArray = $response->getData(true);

        $this->assertEquals('200', $responseArray['status']); 
        $this->assertEquals('Event canceled with success, there are associated tickets', $responseArray['message']); 
    }


}
