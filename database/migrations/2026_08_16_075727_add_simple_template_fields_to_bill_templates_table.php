<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_templates', function (Blueprint $table) {
            $table->string('vat_pan_number')->nullable()->after('phone');
            $table->boolean('calculate_vat')->default(false)->after('show_qr');
            $table->decimal('vat_percentage', 5, 2)->default(13.00)->after('calculate_vat');
            $table->boolean('show_cashier')->default(false)->after('show_customer');
            $table->boolean('show_payment_method')->default(true)->after('show_cash_received');
            $table->string('line_spacing')->default('normal')->after('alignment');
            $table->string('section_spacing')->default('normal')->after('line_spacing');
        });
    }

    public function down(): void
    {
        Schema::table('bill_templates', function (Blueprint $table) {
            $table->dropColumn(['vat_pan_number', 'calculate_vat', 'vat_percentage', 'show_cashier', 'show_payment_method', 'line_spacing', 'section_spacing']);
        });
    }
};