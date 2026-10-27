<?php

declare(strict_types=1);

return ['low_stock_threshold' => (int) env('INVENTORY_LOW_STOCK_THRESHOLD', 10), 'max_items' => (int) env('ORDER_MAX_ITEMS', 50), 'max_line_quantity' => (int) env('ORDER_MAX_LINE_QUANTITY', 100), 'transaction_attempts' => (int) env('ORDER_TRANSACTION_ATTEMPTS', 3), 'per_page' => (int) env('DEFAULT_PER_PAGE', 15), 'currency' => env('STORE_CURRENCY', 'INR'), 'currency_symbol' => env('STORE_CURRENCY_SYMBOL', '₹'), 'tax_rounding' => env('STORE_TAX_ROUNDING', 'half_up')];
