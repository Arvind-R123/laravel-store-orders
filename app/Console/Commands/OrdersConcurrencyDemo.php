<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

final class OrdersConcurrencyDemo extends Command
{
    protected $signature = 'orders:concurrency-demo {--product=} {--requests=5}';

    protected $description = 'Run concurrent one-unit order attempts.';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->error('Concurrency demo requires MySQL or PostgreSQL.');

            return self::FAILURE;
        }
        $product = Product::find($this->option('product')) ?? Product::first();
        if (! $product) {
            $this->error('No product exists.');

            return self::FAILURE;
        }
        $originalStock = (int) $product->stock;
        $product->update(['stock' => 1]);
        $requests = max(1, (int) $this->option('requests'));
        $processes = Process::pool(function (Pool $pool) use ($product, $requests): void {
            for ($i = 0; $i < $requests; $i++) {
                $pool->as("request-{$i}")->command([
                    PHP_BINARY, base_path('artisan'), 'orders:place-one',
                    '--product='.$product->id, '--email="concurrency-'.$i.'@example.com"', '--qty=1',
                ]);
            }
        })->start()->wait();
        $counts = ['success' => 0, 'insufficient_stock' => 0, 'error' => 0];
        foreach ($processes as $process) {
            $payload = json_decode(trim($process->output()), true);
            $status = $payload['status'] ?? 'error';
            $counts[$status] = ($counts[$status] ?? 0) + 1;
        }
        try {
            $finalStock = (int) Product::findOrFail($product->id)->stock;
            $this->table(['status', 'count'], collect($counts)->map(fn (int $count, string $status): array => [$status, $count])->values()->all());
            $this->line('final_stock='.$finalStock);

            return $counts['success'] === 1
                && $counts['insufficient_stock'] === $requests - 1
                && $counts['error'] === 0
                && $finalStock === 0
                ? self::SUCCESS
                : self::FAILURE;
        } finally {
            $product->update(['stock' => $originalStock]);
        }
    }
}
