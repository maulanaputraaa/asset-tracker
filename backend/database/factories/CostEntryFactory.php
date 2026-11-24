<?php

namespace Database\Factories;

use App\Models\CostEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostEntry>
 */
class CostEntryFactory extends Factory
{
    protected $model = CostEntry::class;

    public function definition(): array
    {
        return [
            'category' => fake()->randomElement(['Acquisition', 'Maintenance', 'Repair', 'Sparepart', 'Logistics']),
            'amount' => fake()->randomFloat(2, 100000, 10000000),
            'currency' => 'IDR',
            'transaction_date' => fake()->date(),
            'invoice_number' => fake()->bothify('INV-######'),
        ];
    }
}