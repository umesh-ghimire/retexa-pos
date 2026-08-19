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
        'shop_email',
        'shop_tax_vat',
        'shop_currency',
        'shop_logo_path',
        'default_discount',
        'low_stock_threshold',
        'payment_qr_path',
        // Receipt printer
        'printer_paper_width_mm',
        'printer_alignment',
        'printer_margin_left_mm',
        'printer_margin_right_mm',
        'printer_page_length_mode',
        'printer_page_length_mm',
        'printer_size_preset',
        'printer_font_size_px',
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
        // Other Settings — General
        'business_start_date',
        'default_language',
        'app_timezone',
        'enable_stock_management',
        'show_product_sku',
        'enable_sales_return',
        'enable_notifications',
        'items_per_page',
        // Other Settings — Backup & Data
        'auto_backup',
        'backup_time',
        'keep_backup_for_days',
        // Other Settings — Security
        'require_strong_password',
        'auto_logout_minutes',
        'login_session_limit',
    ];

    public function index()
    {
        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = Setting::get($key);
        }

        // Real, derived values (not fabricated) — same helpers the actual
        // receipt/label rendering uses, so the "current settings" summary
        // on screen always matches what will actually print.
        $printerVars = Setting::printerCssVars();
        $labelVars = Setting::labelCssVars();

        // "Show Logo / Barcode / QR on Receipt" and "Footer Message" are
        // NOT duplicated as separate Setting rows — Bill Designer already
        // owns these per-template. Reading/writing the default template
        // here keeps one single source of truth instead of two that could
        // silently disagree.
        $defaultTemplate = \App\Models\BillTemplate::where('is_default', true)->first();

        $backups = collect(\Illuminate\Support\Facades\Storage::disk('local')->files('backups'))
            ->filter(fn ($path) => str_ends_with($path, '.zip'))
            ->sortByDesc(fn ($path) => \Illuminate\Support\Facades\Storage::disk('local')->lastModified($path))
            ->map(fn ($path) => [
                'name' => basename($path),
                'size' => \Illuminate\Support\Facades\Storage::disk('local')->size($path),
                'date' => \Carbon\Carbon::createFromTimestamp(\Illuminate\Support\Facades\Storage::disk('local')->lastModified($path)),
            ])
            ->values();

        $timezones = \DateTimeZone::listIdentifiers();

        return view('admin.settings.index', compact('settings', 'printerVars', 'labelVars', 'defaultTemplate', 'backups', 'timezones'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => ['nullable', 'string', 'max:255'],
            'shop_address' => ['nullable', 'string', 'max:255'],
            'shop_phone' => ['nullable', 'string', 'max:50'],
            'shop_email' => ['nullable', 'email', 'max:255'],
            'shop_tax_vat' => ['nullable', 'string', 'max:100'],
            'shop_currency' => ['nullable', 'string', 'max:10'],
            'shop_logo' => ['nullable', 'image', 'max:2048'],
            'default_discount' => ['nullable', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'payment_qr' => ['nullable', 'image', 'max:2048'],

            // Receipt printer — free numeric mm entry, printer-independent
            'printer_paper_width_mm' => ['nullable', 'numeric', 'min:20', 'max:200'],
            'printer_alignment' => ['nullable', 'in:left,center,right'],
            'printer_margin_left_mm' => ['nullable', 'numeric', 'min:0', 'max:30'],
            'printer_margin_right_mm' => ['nullable', 'numeric', 'min:0', 'max:30'],
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

            // Other Settings — General
            'business_start_date' => ['nullable', 'date'],
            'default_language' => ['nullable', 'in:en,ne'],
            'app_timezone' => ['nullable', 'timezone'],
            'enable_stock_management' => ['nullable', 'boolean'],
            'show_product_sku' => ['nullable', 'boolean'],
            'enable_sales_return' => ['nullable', 'boolean'],
            'enable_notifications' => ['nullable', 'boolean'],
            'items_per_page' => ['nullable', 'integer', 'min:5', 'max:200'],

            // Other Settings — Backup & Data
            'auto_backup' => ['nullable', 'boolean'],
            'backup_time' => ['nullable', 'date_format:H:i'],
            'keep_backup_for_days' => ['nullable', 'integer', 'min:1', 'max:365'],

            // Other Settings — Security
            'require_strong_password' => ['nullable', 'boolean'],
            'auto_logout_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'login_session_limit' => ['nullable', 'integer', 'min:0', 'max:20'],

            // Other Settings — Receipt & Display (these 4 are NOT stored as
            // Setting rows — they write straight to the default Bill Design,
            // which already owns them, so there's one source of truth).
            'show_logo' => ['nullable', 'boolean'],
            'show_barcode' => ['nullable', 'boolean'],
            'show_qr' => ['nullable', 'boolean'],
            'footer_text' => ['nullable', 'string', 'max:255'],
        ]);

        $billTemplateFields = ['show_logo', 'show_barcode', 'show_qr', 'footer_text'];
        $billTemplateData = [];
        foreach ($billTemplateFields as $field) {
            if (array_key_exists($field, $validated)) {
                $billTemplateData[$field] = $field === 'footer_text' ? $validated[$field] : (bool) $request->boolean($field);
                unset($validated[$field]);
            }
        }

        // Checkboxes that were left unchecked don't appear in the request at
        // all, so they're missing from $validated entirely (not present =
        // false, not "leave unchanged") — set them explicitly.
        foreach (['enable_stock_management', 'show_product_sku', 'enable_sales_return', 'enable_notifications', 'auto_backup', 'require_strong_password'] as $boolKey) {
            $validated[$boolKey] = $request->boolean($boolKey);
        }

        unset($validated['payment_qr']);
        unset($validated['shop_logo']);

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

        if ($request->hasFile('shop_logo')) {
            $oldLogoPath = Setting::get('shop_logo_path');
            if ($oldLogoPath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogoPath);
            }
            $newLogoPath = $request->file('shop_logo')->store('shop-logo', 'public');
            Setting::set('shop_logo_path', $newLogoPath);
        }

        if (! empty($billTemplateData)) {
            $defaultTemplate = \App\Models\BillTemplate::where('is_default', true)->first();
            if ($defaultTemplate) {
                $defaultTemplate->update($billTemplateData);
            }
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    /**
     * Run a real backup on demand (same command the scheduler uses).
     */
    public function backupNow()
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('backup:run');

        if ($exitCode !== 0) {
            return back()->with('error', 'Backup failed. Check the server logs for details.');
        }

        return back()->with('success', 'Backup completed successfully.');
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