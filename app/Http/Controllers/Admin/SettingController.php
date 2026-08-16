<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
   private const KEYS = [
        'shop_name',
        'shop_address',
        'shop_phone',
        'default_discount',
        'low_stock_threshold',
        'payment_qr_path',
        // Receipt printer
        'printer_paper_width_mm',
        'printer_page_length_mode',
        'printer_page_length_mm',
        'printer_size_preset',
        'printer_copies',
        // Label printer
        'label_width_mm',
        'label_height_mm',
        'label_margin_top_mm',
        'label_margin_right_mm',
        'label_margin_bottom_mm',
        'label_margin_left_mm',
        'label_gap_mm',
        'label_size_preset',
        'label_copies',
    ];

    public function index()
    {
        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = Setting::get($key);
        }

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => ['nullable', 'string', 'max:255'],
            'shop_address' => ['nullable', 'string', 'max:255'],
            'shop_phone' => ['nullable', 'string', 'max:50'],
            'default_discount' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'payment_qr' => ['nullable', 'image', 'max:2048'],

            // Receipt printer — free numeric mm entry, printer-independent
            'printer_paper_width_mm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'printer_page_length_mode' => ['nullable', 'in:auto,custom'],
            'printer_page_length_mm' => ['nullable', 'numeric', 'min:20', 'max:500', 'required_if:printer_page_length_mode,custom'],
            'printer_size_preset' => ['nullable', 'in:small,medium,large'],
            'printer_copies' => ['nullable', 'integer', 'min:1', 'max:5'],

            // Label printer — free numeric mm entry
            'label_width_mm' => ['nullable', 'numeric', 'min:10', 'max:200'],
            'label_height_mm' => ['nullable', 'numeric', 'min:10', 'max:200'],
            'label_margin_top_mm' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'label_margin_right_mm' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'label_margin_bottom_mm' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'label_margin_left_mm' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'label_gap_mm' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'label_size_preset' => ['nullable', 'in:small,medium,large'],
            'label_copies' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        unset($validated['payment_qr']);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        if ($request->hasFile('payment_qr')) {
            $oldPath = Setting::get('payment_qr_path');
            if ($oldPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
            $newPath = $request->file('payment_qr')->store('payment-qr', 'public');
            Setting::set('payment_qr_path', $newPath);
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    public function testPrint()
    {
        $template = \App\Models\BillTemplate::where('is_default', true)->first();
        $printerVars = \App\Models\Setting::printerCssVars();

        $sampleSale = [
            'bill_number' => 'TEST-0001',
            'date' => now()->format('Y-m-d'),
            'created_at' => now()->toIso8601String(),
            'customer_name' => 'Test Customer',
            'show_qr' => true,
            'subtotal' => 500,
            'discount' => 0,
            'total' => 500,
            'cash_received' => 500,
            'change_amount' => 0,
            'due_amount' => 0,
            'items' => [
                ['name' => 'Sample Item', 'quantity' => 1, 'unit_price' => 500, 'line_total' => 500, 'sku' => null, 'unit' => null],
            ],
        ];

        return view('admin.settings.test-print', compact('template', 'printerVars', 'sampleSale'));
    }

    /**
     * Print a sample barcode label using the currently saved
     * label printer settings (not tied to any real product).
     */
    public function testLabel()
    {
        $labelVars = \App\Models\Setting::labelCssVars();
        $shopName = \App\Models\Setting::get('shop_name', 'My Shop');

        $sampleLabel = [
            'product_name' => 'Sample Product',
            'price' => '80.00',
            'barcode' => '200000000001',
        ];

        return view('admin.settings.test-label', compact('labelVars', 'shopName', 'sampleLabel'));
    }
}