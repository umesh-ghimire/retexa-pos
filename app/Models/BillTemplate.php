<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillTemplate extends Model
{
    /**
     * The standard section order, used whenever a template
     * hasn't had a custom order saved yet.
     */
    public const DEFAULT_SECTION_ORDER = [
        'header', 'bill_info', 'customer_info', 'items', 'totals', 'payment', 'qr', 'footer',
    ];
    protected $fillable = [
        'created_by',
        'name',
        'is_default',
        'paper_width',
        'font_size',
        'alignment',
        'show_logo',
        'logo_path',
        'section_order',
        'layout',
        'shop_name',
        'address',
        'phone',
        'vat_pan_number',
        'calculate_vat',
        'vat_percentage',
        'show_cashier',
        'show_payment_method',
        'line_spacing',
        'section_spacing',
        'header_text',
        'footer_text',
        'show_customer',
        'canvas_layout',
        'show_bill_number',
        'show_date',
        'show_sku',
        'show_barcode',
        'show_quantity',
        'show_unit',
        'show_price',
        'show_subtotal',
        'show_discount',
        'show_cash_received',
        'show_change',
        'show_qr',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'section_order' => 'array',
        'layout'=>'array',
        'show_logo' => 'boolean',
        'show_customer' => 'boolean',
        'canvas_layout' => 'array',
        'show_bill_number' => 'boolean',
        'show_date' => 'boolean',
        'show_sku' => 'boolean',
        'show_barcode' => 'boolean',
        'show_quantity' => 'boolean',
        'show_unit' => 'boolean',
        'show_price' => 'boolean',
        'show_subtotal' => 'boolean',
        'calculate_vat' => 'boolean',
        'vat_percentage' => 'decimal:2',
        'show_cashier' => 'boolean',
        'show_payment_method' => 'boolean',
        'show_discount' => 'boolean',
        'show_cash_received' => 'boolean',
        'show_change' => 'boolean',
        'show_qr' => 'boolean',
    ];

    /**
     * The admin user who created this template.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * A template can be used by many sales.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get this template's section order, falling back to the
     * standard order if none has been customized yet.
     */
    public function getSectionOrderOrDefault(): array
    {
        return $this->section_order && count($this->section_order) > 0
            ? $this->section_order
            : self::DEFAULT_SECTION_ORDER;
    }

    /**
     * Whether this template has visual Bill Designer layout data.
     *
     * Used by receipt-renderer.js (via the data passed to it) and
     * any controller that needs to branch between the new
     * layout-aware rendering path and the existing section-based
     * rendering path. Templates saved before the visual designer
     * existed will have layout = null and will keep using the
     * existing section-based renderer.
     */
    public function hasVisualLayout(): bool
    {
        return is_array($this->layout)
            && array_key_exists('elements', $this->layout)
            && is_array($this->layout['elements'])
            && count($this->layout['elements']) > 0;
    }

    public function usesCanvasLayout(): bool
    {
        return is_array($this->canvas_layout) && ! empty($this->canvas_layout['elements']);
    }
}