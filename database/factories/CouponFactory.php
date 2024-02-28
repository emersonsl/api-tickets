<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => strtoupper(fake()->word()),
            'quantity' =>  fake()->numberBetween(0, 1000),
            'value' => fake()->randomFloat(2, 0, 500),
            'release_date_time' => fake()->dateTime(),
            'expiration_date_time' => fake()->dateTime(),
            'event_id' => Event::all()->random()->id,
        ];
    }
}
