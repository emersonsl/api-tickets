<?php

namespace App\Http\Resources;

use App\Models\Address;
use App\Models\Batch;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResourceWithBatchWithSector extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::find($this->create_by);
        $batch = Batch::find($this->batch_id);
        $sector = Sector::find($this->sector_id);
        return [
            'id' => $this->id,
            'title' => $this->title,
            'banner_url' => $this->banner_url,
            'date_time' => $this->date_time,
            'address' => Address::find($this->address_id),
            'batch' => [
                'id' => $batch->id,
                'title' => $batch->title,
                'quantity' => $batch->quantity,
                'value' => $batch->value,
                'release_date_time' => $batch->release_date_time,
                'expiration_date_time' => $batch->expiration_date_time
            ],
            'sector' => [
                'id' => $sector->id,
                'title' => $sector->title
            ],
            'create_by'=> [
                'id' => $user->id,
                'name' => $user->name
            ],
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
