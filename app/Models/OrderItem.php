<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'quantity', 'unit_price', 'tax_percent', 'line_subtotal', 'line_tax', 'line_total'];

    protected $casts = ['quantity' => 'integer', 'unit_price' => 'integer', 'line_subtotal' => 'integer', 'line_tax' => 'integer', 'line_total' => 'integer', 'tax_percent' => 'decimal:2'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
