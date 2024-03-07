<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->catchPhrase(),
            'date_time' => fake()->dateTimeBetween('now', '+ 2 month'),
            'address_id' => Address::all()->random()->id,
            'banner_url' => fake()->url(),
            'create_by' => User::all()->random()->id,
        ];
    }
}
