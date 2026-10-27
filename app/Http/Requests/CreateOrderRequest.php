<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return ['customer' => 'required|array', 'customer.email' => 'required|email', 'customer.name' => 'nullable|string|max:255', 'items' => ['required', 'array', 'min:1', 'max:'.config('inventory.max_items')], 'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')], 'items.*.quantity' => 'required|integer|min:1|max:'.config('inventory.max_line_quantity')];
    }
}
