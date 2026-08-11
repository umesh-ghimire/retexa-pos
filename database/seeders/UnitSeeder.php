<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece', 'short_code' => 'pcs'],
            ['name' => 'Kilogram', 'short_code' => 'kg'],
            ['name' => 'Gram', 'short_code' => 'g'],
            ['name' => 'Liter', 'short_code' => 'L'],
            ['name' => 'Milliliter', 'short_code' => 'ml'],
            ['name' => 'Meter', 'short_code' => 'm'],
            ['name' => 'Box', 'short_code' => 'box'],
            ['name' => 'Packet', 'short_code' => 'pkt'],
            ['name' => 'Bottle', 'short_code' => 'btl'],
            ['name' => 'Dozen', 'short_code' => 'dz'],
            ['name' => 'Carton', 'short_code' => 'ctn'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}