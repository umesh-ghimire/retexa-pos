<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Get a setting's value by key, with a fallback default
     * if it hasn't been set yet.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Save (create or update) a setting by key.
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Resolve the current printer settings into the values the
     * print CSS actually uses, with fallbacks matching the
     * already-tested PT210 configuration (72mm, auto length).
     */
   /**
     * Resolve the current printer settings into the values the
     * print CSS actually uses, with fallbacks matching the
     * already-tested PT210 configuration (72mm, auto length).
     */
    public static function printerCssVars(): array
    {
        $paperWidthMm = (float) static::get('printer_paper_width_mm', '72');
        $pageLengthMode = static::get('printer_page_length_mode', 'auto');
        $pageLengthMm = static::get('printer_page_length_mm', '200');
        $sizePreset = static::get('printer_size_preset', 'medium');

        // Font size is no longer a fixed number — it scales with the
        // actual paper width, so a 48mm printer and a 100mm printer
        // don't get identical text. The Print Size preset then nudges
        // that width-based size up or down. Multipliers are tuned so
        // 72mm + Medium still lands on the original PT210 default (21px).
        $multiplierMap = [
            'small' => 0.81,
            'medium' => 1.0,
            'large' => 1.143,
        ];
        $weightMap = [
            'small' => '700',
            'medium' => '800',
            'large' => '800',
        ];
        $multiplier = $multiplierMap[$sizePreset] ?? $multiplierMap['medium'];
        $fontWeight = $weightMap[$sizePreset] ?? $weightMap['medium'];

        $baseFontPx = $paperWidthMm * 0.29;
        $fontPx = (int) round(max(12, min(34, $baseFontPx * $multiplier)));

        // "Auto / Continuous" lets the thermal printer cut after the
        // content ends instead of forcing a fixed page length.
        $length = $pageLengthMode === 'custom' ? $pageLengthMm . 'mm' : 'auto';

        return [
            'width' => $paperWidthMm . 'mm',
            'length' => $length,
            'length_mode' => $pageLengthMode,
            'font_size' => $fontPx . 'px',
            'font_weight' => $fontWeight,
            'copies' => (int) static::get('printer_copies', 1),
        ];
    }

    /**
     * Resolve the current label printer settings into the values the
     * label print CSS uses, with sane defaults matching a standard
     * 50mm x 25mm label.
     */
   /**
     * Resolve the current label printer settings into the values the
     * label print CSS uses, with sane defaults matching a standard
     * 50mm x 25mm label. Font sizes scale with label width, same
     * principle as the receipt printer.
     */
    public static function labelCssVars(): array
    {
        $labelWidthMm = (float) static::get('label_width_mm', '50');
        $sizePreset = static::get('label_size_preset', 'medium');

        $multiplierMap = [
            'small' => 0.85,
            'medium' => 1.0,
            'large' => 1.2,
        ];
        $multiplier = $multiplierMap[$sizePreset] ?? $multiplierMap['medium'];

        $baseShopPx = max(6, min(16, $labelWidthMm * 0.16));
        $baseProductPx = max(7, min(18, $labelWidthMm * 0.18));
        $basePricePx = max(7, min(20, $labelWidthMm * 0.20));

        return [
            'width' => static::get('label_width_mm', '50') . 'mm',
            'height' => static::get('label_height_mm', '25') . 'mm',
            'margin_top' => static::get('label_margin_top_mm', '0') . 'mm',
            'margin_right' => static::get('label_margin_right_mm', '0') . 'mm',
            'margin_bottom' => static::get('label_margin_bottom_mm', '0') . 'mm',
            'margin_left' => static::get('label_margin_left_mm', '0') . 'mm',
            'gap' => static::get('label_gap_mm', '2') . 'mm',
            'shop_font_size' => round($baseShopPx * $multiplier, 1) . 'px',
            'product_font_size' => round($baseProductPx * $multiplier, 1) . 'px',
            'price_font_size' => round($basePricePx * $multiplier, 1) . 'px',
            'copies' => (int) static::get('label_copies', 1),
        ];
    }
}