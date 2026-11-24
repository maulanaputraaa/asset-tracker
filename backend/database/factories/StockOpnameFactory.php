<?php

namespace Database\Factories;

use App\Models\StockOpname;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockOpname>
 */
class StockOpnameFactory extends Factory
{
    protected $model = StockOpname::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true) . ' ' . now()->format('Y-m-d'),
            'status' => fake()->randomElement(['Draft', 'In Progress', 'Completed']),
        ];
    }
}