<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendOrderConfirmation;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

final class OrderValidationAndJobTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith([0])]
    #[TestWith([-1])]
    #[TestWith(['abc'])]
    #[TestWith([1.5])]
    public function test_invalid_quantity_returns_422(mixed $quantity): void
    {
        $product = Product::factory()->create();

        $this->postJson('/api/v1/orders', $this->payload([
            ['product_id' => $product->id, 'quantity' => $quantity],
        ]))->assertUnprocessable();
    }

    public function test_empty_items_returns_422(): void
    {
        $this->postJson('/api/v1/orders', $this->payload([]))
            ->assertUnprocessable();
    }

    public function test_missing_items_returns_422(): void
    {
        $this->postJson('/api/v1/orders', ['customer' => ['email' => 'new@example.com', 'name' => 'New Customer']])
            ->assertUnprocessable();
    }

    public function test_nonexistent_product_returns_422(): void
    {
        $this->postJson('/api/v1/orders', $this->payload([
            ['product_id' => 999999, 'quantity' => 1],
        ]))->assertUnprocessable();
    }

    public function test_malformed_customer_email_returns_422(): void
    {
        $this->postJson('/api/v1/orders', $this->payload([
            ['product_id' => Product::factory()->create()->id, 'quantity' => 1],
        ], 'not-an-email'))->assertUnprocessable();
    }

    public function test_new_customer_without_name_returns_422(): void
    {
        $this->postJson('/api/v1/orders', [
            'customer' => ['email' => 'new@example.com'],
            'items' => [['product_id' => Product::factory()->create()->id, 'quantity' => 1]],
        ])->assertUnprocessable();
    }

    public function test_successful_order_dispatches_confirmation_once_after_commit(): void
    {
        $product = Product::factory()->create(['stock' => 2]);
        Queue::fake();

        $response = $this->postJson('/api/v1/orders', $this->payload([
            ['product_id' => $product->id, 'quantity' => 1],
        ]))->assertCreated();

        Queue::assertPushed(SendOrderConfirmation::class, function (SendOrderConfirmation $job) use ($response): bool {
            $this->assertDatabaseHas('orders', ['id' => $job->orderId]);

            return $job->orderId === $response->json('data.id');
        });
        Queue::assertPushedTimes(SendOrderConfirmation::class, 1);
    }

    public function test_insufficient_stock_dispatches_no_confirmation(): void
    {
        $product = Product::factory()->create(['stock' => 1]);
        Queue::fake();

        $this->postJson('/api/v1/orders', $this->payload([
            ['product_id' => $product->id, 'quantity' => 2],
        ]))->assertUnprocessable();

        Queue::assertNothingPushed();
    }

    private function payload(array $items, string $email = 'new@example.com'): array
    {
        return [
            'customer' => ['email' => $email, 'name' => 'New Customer'],
            'items' => $items,
        ];
    }
}
