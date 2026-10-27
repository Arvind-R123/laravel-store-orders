<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Services\OrderNumberGenerator;
use App\Services\PricingCalculator;
use Tests\TestCase;

class PricingCalculatorTest extends TestCase
{
    public function test_pricing_rounds_half_up_and_handles_zero_tax(): void
    {
        $p = new Product(['price' => 1, 'tax_percent' => 50]);
        $this->assertSame(1, (new PricingCalculator)->line($p, 1)['line_tax']);
        $p->tax_percent = '0.00';
        $this->assertSame(0, (new PricingCalculator)->line($p, 1000000)['line_tax']);
    }

    public function test_order_number(): void
    {
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{6}$/', (new OrderNumberGenerator)->generate());
    }
}
