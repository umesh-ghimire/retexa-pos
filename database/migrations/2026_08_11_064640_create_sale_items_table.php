<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();

            // Nullable on purpose: manual calculator entries (e.g. "Item 1") have no product/unit
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();

            $table->string('item_name');
            $table->decimal('quantity', 12, 3)->default(1);   // supports 2.5 kg, 1.5 L, etc.
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);              // quantity × unit_price

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};