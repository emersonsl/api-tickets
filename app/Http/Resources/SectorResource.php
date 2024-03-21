<?php

namespace App\Http\Resources;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectorResource extends JsonResource
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
            'title' => $this->title,
            'event' => [
                'id' => $event->id,
                'title' => $event->title
            ],
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
