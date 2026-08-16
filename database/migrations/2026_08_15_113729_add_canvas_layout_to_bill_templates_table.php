<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_templates', function (Blueprint $table) {
            $table->json('canvas_layout')->nullable()->after('section_order');
        });
    }

    public function down(): void
    {
        Schema::table('bill_templates', function (Blueprint $table) {
            $table->dropColumn('canvas_layout');
        });
    }
};