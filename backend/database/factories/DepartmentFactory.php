<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['IT', 'Produksi', 'Finance', 'HRD', 'Logistik']),
            'code' => strtoupper(fake()->bothify('DEPT-##')),
        ];
    }
}