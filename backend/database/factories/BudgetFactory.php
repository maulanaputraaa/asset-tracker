<?php

namespace Database\Factories;

use App\Models\Budget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'allocated_amount' => fake()->randomFloat(2, 10000000, 500000000),
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ];
    }
}