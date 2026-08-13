<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
   public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            BillTemplateSeeder::class,
        ]);

        \App\Models\Setting::set('next_internal_barcode', '200000000001');
    }
}