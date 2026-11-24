<?php

namespace Database\Factories;

use App\Models\MaintenanceSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceSchedule>
 */
class MaintenanceScheduleFactory extends Factory
{
    protected $model = MaintenanceSchedule::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'interval_days' => fake()->numberBetween(7, 365),
            'last_run_date' => now()->subDays(fake()->numberBetween(1, 30)),
            'next_due_date' => now()->addDays(fake()->numberBetween(1, 60)),
        ];
    }
}