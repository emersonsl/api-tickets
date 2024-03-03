<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lote>
 */
class BatchFactory extends Factory
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
            'event_id' => Event::all()->random()->id,
            'sector_id' => Sector::all()->random()->id,
            'quantity' => fake()->numberBetween(0, 1000),
            'value' => fake()->numberBetween(0, 1000),
            'release_date_time' => fake()->dateTime(),
            'expiration_date_time' => fake()->dateTime(),
        ];
    }
}
