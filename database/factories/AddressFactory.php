<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'street' => fake()->streetName(),
            'district' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
            'post_code' => fake()->postcode(),
            'complement' => fake()->citySuffix(),
        ];
    }
}
