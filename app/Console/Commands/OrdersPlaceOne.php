<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Orders\CreateOrder;
use App\Exceptions\InsufficientStock;
use App\Models\Product;
use Illuminate\Console\Command;
use Throwable;

final class OrdersPlaceOne extends Command
{
    protected $signature = 'orders:place-one {--product=} {--email=} {--qty=1}';

    protected $description = 'Place one order attempt for the concurrency demonstration.';

    protected $hidden = true;

    public function handle(CreateOrder $createOrder): int
    {
        try {
            $product = Product::findOrFail((int) $this->option('product'));
            $createOrder->handle([
                'customer' => ['email' => (string) $this->option('email'), 'name' => 'Concurrency Demo'],
                'items' => [['product_id' => $product->id, 'quantity' => (int) $this->option('qty')]],
            ]);
            $this->line(json_encode(['status' => 'success', 'message' => 'Order created'], JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        } catch (InsufficientStock $exception) {
            $this->line(json_encode(['status' => 'insufficient_stock', 'message' => $exception->getMessage()], JSON_THROW_ON_ERROR));

            return 1;
        } catch (Throwable $exception) {
            $this->line(json_encode(['status' => 'error', 'message' => $exception->getMessage()], JSON_THROW_ON_ERROR));

            return 2;
        }
    }
}
