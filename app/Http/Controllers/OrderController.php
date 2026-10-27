<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrder;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\CustomerOrdersRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function store(CreateOrderRequest $request, CreateOrder $action): JsonResponse
    {
        return (new OrderResource($action->handle($request->validated())))->response()->setStatusCode(201);
    }

    public function history(CustomerOrdersRequest $request)
    {
        $customer = Customer::where('email', $request->validated('email'))->firstOrFail();

        return OrderResource::collection($customer->orders()->with(['customer', 'items.product'])->latest()->paginate(config('inventory.per_page', 15)));
    }

    public function products()
    {
        return ProductResource::collection(Product::orderBy('name')->get());
    }

    public function lowStock(Request $request)
    {
        $threshold = $request->integer('threshold', config('inventory.low_stock_threshold'));
        abort_if($threshold < 0, 422, 'Invalid threshold');

        return ProductResource::collection(Product::lowStock($threshold)->orderBy('stock')->get());
    }
}
