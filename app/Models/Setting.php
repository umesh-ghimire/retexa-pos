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
        $alignment = static::get('printer_alignment', 'left');
        $marginLeftMm = (float) static::get('printer_margin_left_mm', '0');
        $marginRightMm = (float) static::get('printer_margin_right_mm', '0');

        $printableWidthMm = max(10, $paperWidthMm - $marginLeftMm - $marginRightMm);

        // Font size is no longer a fixed number — it scales with the
        // actual PRINTABLE width (paper width minus margins), so a
        // 48mm printer and a 100mm printer don't get identical text,
        // and adding margins doesn't silently overflow the page. The
        // Print Size preset then nudges that width-based size up or
        // down. Multipliers are tuned so 72mm + 0 margins + Medium
        // still lands on the original PT210 default (21px).
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

        $baseFontPx = $printableWidthMm * 0.29;
        $autoFontPx = (int) round(max(12, min(34, $baseFontPx * $multiplier)));

        // Centralized "Receipt Text Size" override. When the user has
        // explicitly set a value (Printer Settings), it wins outright
        // and every consumer (Test Print, /billing, /admin/bills) uses
        // exactly that pixel size — no separate calculation per page.
        // Until it's set, behavior is unchanged: falls back to the
        // existing width-based auto calculation above (still 21px for
        // the default 72mm / 0 margins / Medium configuration).
        $explicitFontPx = static::get('printer_font_size_px', null);
        $fontPx = ($explicitFontPx !== null && $explicitFontPx !== '')
            ? (int) round(max(10, min(40, (float) $explicitFontPx)))
            : $autoFontPx;

        // "Auto / Continuous" lets the thermal printer cut after the
        // content ends instead of forcing a fixed page length.
        $length = $pageLengthMode === 'custom' ? $pageLengthMm . 'mm' : 'auto';

        return [
            'width' => $paperWidthMm . 'mm',
            'printable_width' => $printableWidthMm . 'mm',
            'length' => $length,
            'length_mode' => $pageLengthMode,
            'alignment' => in_array($alignment, ['left', 'center', 'right'], true) ? $alignment : 'left',
            'margin_left' => $marginLeftMm . 'mm',
            'margin_right' => $marginRightMm . 'mm',
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