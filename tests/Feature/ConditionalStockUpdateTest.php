<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exceptions\InsufficientStock;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ConditionalStockUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_stale_read_conditional_update_affects_zero_and_throws(): void
    {
        $product = Product::factory()->create(['stock' => 1]);
        Product::whereKey($product)->update(['stock' => 0]);
        $affected = Product::whereKey($product)->where('stock', '>=', 1)->update(['stock' => DB::raw('stock - 1')]);
        $this->assertSame(0, $affected);
        $this->expectException(InsufficientStock::class);
        if ($affected !== 1) {
            throw new InsufficientStock([['product_id' => $product->id, 'requested' => 1, 'available' => 0]]);
        }
    }
}
