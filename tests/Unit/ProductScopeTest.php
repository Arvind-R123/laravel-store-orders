<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_is_strictly_below_threshold(): void
    {
        Product::factory()->create(['stock' => 9]);
        Product::factory()->create(['stock' => 10]);
        $this->assertCount(1, Product::lowStock(10)->get());
    }
}
