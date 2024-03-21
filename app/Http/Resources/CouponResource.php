<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $event = Event::find($this->event_id);
        return [
            'id' => $this->id,
            'key' => $this->key,
            'quantity' => $this->quantity,
            'value' => $this->value,
            'event' => [
                'id' => $event->id,
                'title' => $event->title
            ],
            'release_date_time' => $this->release_date_time,
            'expiration_date_time' => $this->expiration_date_time,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
