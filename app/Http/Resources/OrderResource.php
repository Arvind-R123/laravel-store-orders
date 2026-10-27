<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'order_number' => $this->order_number, 'status' => $this->status->value, 'customer' => $this->whenLoaded('customer'), 'items' => $this->items->map(fn ($i) => ['product' => ['id' => $i->product->id, 'name' => $i->product->name, 'code' => $i->product->code], 'quantity' => $i->quantity, 'unit_price' => number_format($i->unit_price / 100, 2, '.', ''), 'line_subtotal' => number_format($i->line_subtotal / 100, 2, '.', ''), 'line_tax' => number_format($i->line_tax / 100, 2, '.', ''), 'line_total' => number_format($i->line_total / 100, 2, '.', '')]), 'subtotal' => number_format($this->subtotal / 100, 2, '.', ''), 'tax_total' => number_format($this->tax_total / 100, 2, '.', ''), 'grand_total' => number_format($this->grand_total / 100, 2, '.', ''), 'created_at' => $this->created_at?->toISOString()];
    }
}
