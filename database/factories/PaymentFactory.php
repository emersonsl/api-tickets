<?php

namespace Database\Factories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ticket = Ticket::all()->random();
        return [
            'ticket_id' => $ticket->id,
            'amount' => $ticket->amount,
            'status' => fake()->numberBetween(0, 1),
            'description' => 'Ref: ' . $ticket->id,
            'paid_at' => fake()->dateTime(),
            'hash' => fake()->word(),
            'reference_id' => fake()->word(),
            'payment' => fake()->word(),
            'expiration_at' => fake()->dateTime()
        ];
    }
}
