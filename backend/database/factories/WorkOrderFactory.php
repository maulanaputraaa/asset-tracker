<?php

namespace Database\Factories;

use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['Preventive', 'Corrective', 'Inspection']),
            'status' => fake()->randomElement(['Open', 'Assigned', 'In Progress']),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'estimated_cost' => fake()->randomFloat(2, 100000, 5000000),
            'actual_cost' => 0,
            'estimated_hours' => fake()->numberBetween(1, 40),
            'due_date' => now()->addDays(fake()->numberBetween(1, 30)),
        ];
    }
}
