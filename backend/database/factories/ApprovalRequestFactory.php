<?php

namespace Database\Factories;

use App\Models\ApprovalRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApprovalRequest>
 */
class ApprovalRequestFactory extends Factory
{
    protected $model = ApprovalRequest::class;

    public function definition(): array
    {
        return [
            'current_step' => 1,
            'status' => fake()->randomElement(['Pending', 'Approved', 'Rejected']),
        ];
    }
}