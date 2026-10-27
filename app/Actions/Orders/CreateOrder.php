<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Exceptions\InsufficientStock;
use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\PricingCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateOrder
{
    public function __construct(private PricingCalculator $pricing) {}

    public function handle(array $data): Order
    {
        $quantities = [];
        foreach ($data['items'] as $item) {
            $quantities[$item['product_id']] = ($quantities[$item['product_id']] ?? 0) + $item['quantity'];
        } $order = DB::transaction(function () use ($data, $quantities) {
            $customer = Customer::firstOrCreate(['email' => $data['customer']['email']], ['name' => $data['customer']['name'] ?? '']);
            $products = Product::whereIn('id', array_keys($quantities))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $bad = [];
            foreach ($quantities as $id => $qty) {
                $p = $products->get($id);
                if (! $p || $p->stock < $qty) {
                    $bad[] = ['product_id' => $id, 'requested' => $qty, 'available' => $p?->stock ?? 0];
                }
            }if ($bad) {
                throw new InsufficientStock($bad);
            }$lines = [];
            $sub = $tax = 0;
            foreach ($quantities as $id => $qty) {
                $p = $products[$id];
                $line = $this->pricing->line($p, $qty);
                $affected = Product::whereKey($id)->where('stock', '>=', $qty)->update(['stock' => DB::raw('stock - '.$qty)]);
                if ($affected !== 1) {
                    throw new InsufficientStock([['product_id' => $id, 'requested' => $qty, 'available' => $p->stock]]);
                }$lines[$id] = [$p, $line];
                $sub += $line['line_subtotal'];
                $tax += $line['line_tax'];
            }$order = Order::create(['order_number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)), 'customer_id' => $customer->id, 'subtotal' => $sub, 'tax_total' => $tax, 'grand_total' => $sub + $tax, 'status' => 'confirmed']);
            foreach ($lines as $pair) {
                $order->items()->create(['product_id' => $pair[0]->id] + $pair[1]);
            }

return $order;
        }, config('inventory.transaction_attempts'));
        SendOrderConfirmation::dispatch($order->id)->afterCommit();

        return $order->load(['customer', 'items.product']);
    }
}
