<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code')->unique();
            $t->unsignedBigInteger('price');
            $t->decimal('tax_percent', 5, 2);
            $t->unsignedInteger('stock')->index();
            $t->timestamps();
        });
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamps();
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_number')->unique();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->unsignedBigInteger('subtotal');
            $t->unsignedBigInteger('tax_total');
            $t->unsignedBigInteger('grand_total');
            $t->string('status')->default('confirmed');
            $t->timestamps();
            $t->index(['customer_id', 'created_at']);
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->restrictOnDelete();
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('unit_price');
            $t->decimal('tax_percent', 5, 2);
            $t->unsignedBigInteger('line_subtotal');
            $t->unsignedBigInteger('line_tax');
            $t->unsignedBigInteger('line_total');
            $t->timestamps();
            $t->unique(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('products');
    }
};
