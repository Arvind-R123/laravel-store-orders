<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class SendOrderConfirmation implements ShouldQueue
{
    use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(public readonly int $orderId) {}

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(): void
    {
        $order = Order::with('customer')->findOrFail($this->orderId);
        Log::channel(config('logging.default'))->info('order_confirmation', ['order_id' => $order->id, 'order_number' => $order->order_number, 'email' => $order->customer->email]);
    }

    public function failed(?\Throwable $e): void
    {
        Log::error('order_confirmation_failed', ['order_id' => $this->orderId, 'error' => $e?->getMessage()]);
    }
}
