<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class InsufficientStock extends RuntimeException
{
    public function __construct(public readonly array $items)
    {
        parent::__construct('Insufficient stock.');
    }
}
