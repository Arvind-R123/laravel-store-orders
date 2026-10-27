<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Product::factory()->createMany([['name' => 'Toothpaste', 'code' => 'TP-001', 'price' => 8500, 'tax_percent' => 18, 'stock' => 6], ['name' => 'Biscuits', 'code' => 'BS-001', 'price' => 3000, 'tax_percent' => 5, 'stock' => 40], ['name' => 'Milk', 'code' => 'MK-001', 'price' => 6000, 'tax_percent' => 5, 'stock' => 8], ['name' => 'Bread', 'code' => 'BR-001', 'price' => 4500, 'tax_percent' => 5, 'stock' => 7], ['name' => 'Eggs (12)', 'code' => 'EG-001', 'price' => 9000, 'tax_percent' => 5, 'stock' => 3]]);
        Customer::factory(10)->create();
    }
}
