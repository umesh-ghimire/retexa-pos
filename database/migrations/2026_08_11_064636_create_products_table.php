<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();

            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();

            $table->decimal('price', 12, 2);              // selling price
            $table->decimal('cost_price', 12, 2)->nullable();

            $table->decimal('stock', 12, 3)->default(0);            // supports fractional stock (e.g. 2.5 kg)
            $table->decimal('min_stock_level', 12, 3)->default(0);

            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};