<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales MODIFY payment_method ENUM('cash', 'qr', 'credit') NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales MODIFY payment_method ENUM('cash', 'qr') NOT NULL DEFAULT 'cash'");
    }
};