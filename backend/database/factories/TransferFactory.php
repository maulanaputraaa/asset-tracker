<?php

namespace Database\Factories;

use App\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(['Draft', 'Pending', 'Approved', 'In Transit']),
            'reason' => fake()->sentence(),
            'planned_date' => now()->addDays(fake()->numberBetween(1, 14)),
        ];
    }
}