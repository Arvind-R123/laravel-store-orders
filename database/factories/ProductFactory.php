<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return ['name' => $this->faker->words(2, true), 'code' => strtoupper($this->faker->unique()->bothify('??-###')), 'price' => $this->faker->numberBetween(1000, 10000), 'tax_percent' => 5, 'stock' => 20];
    }

    public function lowStock(): static
    {
        return $this->state(['stock' => 5]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }
}
