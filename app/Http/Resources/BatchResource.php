<?php

namespace App\Http\Resources;

use App\Models\Event;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $event = Event::find($this->event_id);
        $sector = Sector::find($this->sector_id);
        return [
            'id' => $this->id,
            'title' => $this->title,
            'quantity' => $this->quantity,
            'value' => $this->value,
            'event' => [
                'id' => $event->id,
                'title' => $event->title
            ],
            'sector' => [
                'id' => $sector->id,
                'title' => $sector->title
            ],
            'release_date_time' => $this->release_date_time,
            'expiration_date_time' => $this->expiration_date_time,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
