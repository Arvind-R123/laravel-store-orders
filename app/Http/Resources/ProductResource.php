<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'code' => $this->code, 'price' => number_format($this->price / 100, 2, '.', ''), 'tax_percent' => (string) $this->tax_percent, 'stock' => $this->stock];
    }
}
