<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'General',
            'Beverages',
            'Groceries',
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}