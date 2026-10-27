<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['order_number', 'customer_id', 'subtotal', 'tax_total', 'grand_total', 'status'];

    protected $casts = ['subtotal' => 'integer', 'tax_total' => 'integer', 'grand_total' => 'integer', 'status' => OrderStatus::class];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
