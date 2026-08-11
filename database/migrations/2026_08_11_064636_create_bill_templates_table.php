<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->boolean('is_default')->default(false);

            $table->enum('paper_width', ['58mm', '80mm'])->default('80mm');
            $table->enum('font_size', ['small', 'medium', 'large'])->default('medium');
            $table->enum('alignment', ['left', 'center', 'right'])->default('left');

            $table->boolean('show_logo')->default(false);
            $table->string('shop_name')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('header_text')->nullable();
            $table->string('footer_text')->default('THANK YOU / VISIT AGAIN');

            $table->boolean('show_customer')->default(true);
            $table->boolean('show_bill_number')->default(true);
            $table->boolean('show_date')->default(true);
            $table->boolean('show_sku')->default(false);
            $table->boolean('show_quantity')->default(true);
            $table->boolean('show_unit')->default(true);
            $table->boolean('show_price')->default(true);
            $table->boolean('show_subtotal')->default(true);
            $table->boolean('show_discount')->default(true);
            $table->boolean('show_cash_received')->default(true);
            $table->boolean('show_change')->default(true);
            $table->boolean('show_qr')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_templates');
    }
};