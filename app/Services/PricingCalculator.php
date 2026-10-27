<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;

final class PricingCalculator
{
    public function line(Product $p, int $quantity): array
    {
        $subtotal = $p->price * $quantity;
        $tax = (int) round($subtotal * ((float) $p->tax_percent / 100), 0, PHP_ROUND_HALF_UP);

        return ['quantity' => $quantity, 'unit_price' => $p->price, 'tax_percent' => $p->tax_percent, 'line_subtotal' => $subtotal, 'line_tax' => $tax, 'line_total' => $subtotal + $tax];
    }
}
