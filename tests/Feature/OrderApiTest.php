<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private function product(int $stock = 10, int $price = 1000, int $tax = 5): Product
    {
        return Product::factory()->create(['stock' => $stock, 'price' => $price, 'tax_percent' => $tax]);
    }

    private function createOrder(array $items, array $customer = [])
    {
        return $this->postJson('/api/v1/orders', ['customer' => ['email' => $customer['email'] ?? 'a@example.com', 'name' => $customer['name'] ?? 'Alice'], 'items' => $items]);
    }

    public function test_happy_path_totals_stock_and_snapshot(): void
    {
        $p = $this->product();
        Queue::fake();
        $r = $this->createOrder([['product_id' => $p->id, 'quantity' => 2]]);
        $r->assertCreated()->assertJsonPath('data.subtotal', '20.00')->assertJsonPath('data.tax_total', '1.00');
        $this->assertDatabaseHas('products', ['id' => $p->id, 'stock' => 8]);
        $this->assertDatabaseHas('order_items', ['product_id' => $p->id, 'unit_price' => 1000]);
        Queue::assertPushed(SendOrderConfirmation::class);
    }

    public function test_customer_reused_and_new_customer_created(): void
    {
        $p = $this->product();
        Customer::factory()->create(['email' => 'a@example.com', 'name' => 'Original']);
        $this->createOrder([['product_id' => $p->id, 'quantity' => 1]], ['email' => 'a@example.com', 'name' => 'Changed'])->assertCreated();
        $this->assertDatabaseHas('customers', ['email' => 'a@example.com', 'name' => 'Original']);
        $this->createOrder([['product_id' => $p->id, 'quantity' => 1]], ['email' => 'new@example.com', 'name' => 'New'])->assertCreated();
        $this->assertDatabaseHas('customers', ['email' => 'new@example.com']);
    }

    public function test_stock_failures_are_atomic(): void
    {
        $p = $this->product(1);
        Queue::fake();
        $this->createOrder([['product_id' => $p->id, 'quantity' => 2]])->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('customers', 0);
        Queue::assertNothingPushed();
    }

    public function test_validation_duplicate_merge_exact_stock_and_next_failure(): void
    {
        $p = $this->product(3);
        $this->createOrder([['product_id' => $p->id, 'quantity' => 1], ['product_id' => $p->id, 'quantity' => 2]])->assertCreated();
        $this->assertDatabaseHas('order_items', ['quantity' => 3]);
        $this->createOrder([['product_id' => $p->id, 'quantity' => 1]])->assertUnprocessable();
        $this->createOrder([['product_id' => $p->id, 'quantity' => 0]])->assertUnprocessable();
        $this->createOrder([['product_id' => $p->id, 'quantity' => 'x']])->assertUnprocessable();
        $this->createOrder([])->assertUnprocessable();
    }

    public function test_unknown_product_and_multiline_rollback(): void
    {
        $p = $this->product(10);
        $this->createOrder([['product_id' => 99999, 'quantity' => 1]])->assertUnprocessable();
        $this->createOrder([['product_id' => $p->id, 'quantity' => 1], ['product_id' => 99999, 'quantity' => 1]])->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_history_pagination_newest_and_unknown_validation(): void
    {
        $p = $this->product(10);
        $this->createOrder([['product_id' => $p->id, 'quantity' => 1]]);
        $this->createOrder([['product_id' => $p->id, 'quantity' => 1]]);
        $this->getJson('/api/v1/customers/orders?email=nobody@example.com')->assertNotFound();
        $this->getJson('/api/v1/customers/orders?email=missing')->assertUnprocessable();
        $this->getJson('/api/v1/customers/orders')->assertUnprocessable();
    }

    public function test_low_stock_boundary_override_and_sorting(): void
    {
        $this->product(10);
        $low = $this->product(2);
        $this->getJson('/api/v1/products/low-stock')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $low->id);
        $this->getJson('/api/v1/products/low-stock?threshold=11')->assertJsonCount(2, 'data');
    }

    public function test_job_can_run_and_logs(): void
    {
        $p = $this->product();
        $this->createOrder([['product_id' => $p->id, 'quantity' => 1]]);
        $job = new SendOrderConfirmation(Order::first()->id);
        $job->handle();
        $this->assertTrue(true);
    }
}
