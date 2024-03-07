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
        $release_date_time = fake()->dateTimeBetween('now', '+ 30 day');
        $expiration_date_time = fake()->dateTimeBetween($release_date_time, '+ 30 day');
        
        return [
            'title' => fake()->catchPhrase(),
            'event_id' => Event::all()->random()->id,
            'sector_id' => Sector::all()->random()->id,
            'quantity' => fake()->numberBetween(0, 1000),
            'value' => fake()->numberBetween(0, 1000),
            'release_date_time' => $release_date_time,
            'expiration_date_time' => $expiration_date_time
        ];
    }
}
