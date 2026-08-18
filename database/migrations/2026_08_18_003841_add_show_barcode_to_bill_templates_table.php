<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_templates', function (Blueprint $table) {
            $table->boolean('show_barcode')->default(false)->after('show_sku');
        });
    }

    public function down(): void
    {
        Schema::table('bill_templates', function (Blueprint $table) {
            $table->dropColumn('show_barcode');
        });
    }
};