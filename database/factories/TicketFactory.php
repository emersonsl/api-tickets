<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $value = fake()->randomFloat(2, 0, 500);
        $value_discount = fake()->randomFloat(2, 0, $value);
        $amount = $value - $value_discount;
        $batch = Batch::all()->random();
        return [
            'user_id' => User::all()->random()->id,
            'batch_id' => $batch->id,
            'coupon_id' => Coupon::where('event_id', $batch->event_id)->random()->id ?: null,
            'value' => $value,
            'value_discount' => $value_discount,
            'amount' => $amount
        ];
    }
}
