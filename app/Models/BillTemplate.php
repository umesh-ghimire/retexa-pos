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
        'shop_name',
        'address',
        'phone',
        'header_text',
        'footer_text',
        'show_customer',
        'show_bill_number',
        'show_date',
        'show_sku',
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
        'show_logo' => 'boolean',
        'show_customer' => 'boolean',
        'show_bill_number' => 'boolean',
        'show_date' => 'boolean',
        'show_sku' => 'boolean',
        'show_quantity' => 'boolean',
        'show_unit' => 'boolean',
        'show_price' => 'boolean',
        'show_subtotal' => 'boolean',
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
}