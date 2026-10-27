<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

final class OrderNumberGenerator
{
    public function generate(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
    }
}
