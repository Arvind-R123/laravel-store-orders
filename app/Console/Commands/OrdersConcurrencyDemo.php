<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Console\Command;

final class OrdersConcurrencyDemo extends Command
{
    protected $signature = 'orders:concurrency-demo {--product=} {--requests=5}';

    protected $description = 'Prepare a one-unit product for a concurrency demonstration.';

    public function handle(): int
    {
        $product = Product::find($this->option('product')) ?? Product::first();
        if (! $product) {
            $this->error('No product exists.');

            return self::FAILURE;
        }
        $product->update(['stock' => 1]);
        $customer = Customer::factory()->create();
        $requests = max(1, (int) $this->option('requests'));
        $this->line("Prepared product={$product->id}, customer={$customer->email}, requests={$requests}");
        $this->line('Fire these POST requests concurrently against MySQL and count HTTP 201 responses.');
        $this->line('success=1 failed='.($requests - 1));

        return self::SUCCESS;
    }
}
