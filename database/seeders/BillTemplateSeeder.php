<?php

namespace Database\Seeders;

use App\Models\BillTemplate;
use Illuminate\Database\Seeder;

class BillTemplateSeeder extends Seeder
{
    public function run(): void
    {
        BillTemplate::create([
            'name' => 'Default Receipt',
            'is_default' => true,
            'paper_width' => '80mm',
            'font_size' => 'medium',
            'alignment' => 'left',
            'show_logo' => false,
            'shop_name' => 'ABC Store',
            'address' => null,
            'phone' => null,
            'header_text' => null,
            'footer_text' => 'THANK YOU / VISIT AGAIN',
            'show_customer' => true,
            'show_bill_number' => true,
            'show_date' => true,
            'show_sku' => false,
            'show_quantity' => true,
            'show_unit' => true,
            'show_price' => true,
            'show_subtotal' => true,
            'show_discount' => true,
            'show_cash_received' => true,
            'show_change' => true,
            'show_qr' => true,
        ]);
    }
}