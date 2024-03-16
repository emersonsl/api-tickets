<?php

namespace App\Http\Resources;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::find($this->create_by);
        return [
            'id' => $this->id,
            'title' => $this->title,
            'date_time' => $this->date_time,
            'address' => Address::find($this->address_id),
            'banner_url' => $this->banner_url,
            'create_by'=> [
                'id' => $user->id,
                'name' => $user->name
            ],
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
