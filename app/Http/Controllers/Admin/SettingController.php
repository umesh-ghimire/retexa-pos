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
}