<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'price', 'tax_percent', 'stock'];

    protected $casts = ['price' => 'integer', 'stock' => 'integer', 'tax_percent' => 'decimal:2'];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeLowStock(Builder $q, int $threshold): Builder
    {
        return $q->where('stock', '<', $threshold);
    }
}
