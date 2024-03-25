<?php

namespace App\Http\Resources;

use App\Models\Batch;
use App\Models\Coupon;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::find($this->user_id);
        $batch = Batch::find($this->batch_id);
        $sector = Sector::find($batch->sector_id);
        $coupon = Coupon::find($this->coupon_id);
        return [
            'id' => $this->id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name
            ],
            'batch' => [
                'id' => $batch->id,
                'title' => $batch->title,
                'sector' => [
                    'id' => $sector->id,
                    'title' => $sector->title
                ]
            ],
            'coupon' => !$coupon ? null : [
                'id' => $coupon->id,
                'key' => $coupon->key,
            ],
            'value' => $this->value,
            'value_discount' => $this->value_discount,
            'amount' => $this->amount,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
