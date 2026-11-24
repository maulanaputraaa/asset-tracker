<?php

namespace Database\Factories;

use App\Models\ApprovalWorkflow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApprovalWorkflow>
 */
class ApprovalWorkflowFactory extends Factory
{
    protected $model = ApprovalWorkflow::class;

    public function definition(): array
    {
        return [
            'request_type' => fake()->randomElement(['transfer', 'disposal', 'work_order', 'procurement']),
            'name' => fake()->words(3, true),
            'is_active' => true,
        ];
    }
}